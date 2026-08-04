<?php

namespace Modules\Transfer\Services\Translation;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Throwable;

class RapidApiTranslationService
{
    private const PROMPT_VERSION = '2026-07-12-v2';

    private const RESPONSE_CONTENT_PATHS = [
        'result',
        'response',
        'content',
        'answer',
        'message.content',
        'data.result',
        'data.response',
        'data.content',
        'data.message.content',
        'choices.0.message.content',
        'choices.0.text',
    ];

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly TranslationLogger $translationLogger,
        private readonly array $configuration
    ) {
    }

    public function translate(
        array $content,
        array $targetLocales = ['en', 'ru'],
        string $sourceLocale = 'az',
        array $logContext = []
    ): array {
        $this->assertConfiguration();

        if ($content === []) {
            throw new RuntimeException(
                'Translation content cannot be empty.'
            );
        }

        $normalizedSourceLocale = $this->normalizeLocale(
            $sourceLocale
        );

        $normalizedTargetLocales = $this->normalizeTargetLocales(
            $targetLocales
        );

        if (
            in_array(
                $normalizedSourceLocale,
                $normalizedTargetLocales,
                true
            )
        ) {
            throw new RuntimeException(
                'The source locale cannot also be included in the target locales.'
            );
        }

        $encodedContent = $this->encodeJson(
            $content,
            'Unable to encode the translation content as JSON.'
        );

        $this->assertInputSize($encodedContent);

        $cacheKey = $this->buildCacheKey(
            encodedContent: $encodedContent,
            sourceLocale: $normalizedSourceLocale,
            targetLocales: $normalizedTargetLocales
        );

        $requestCallback = fn (): array => $this->requestTranslation(
            content: $content,
            encodedContent: $encodedContent,
            sourceLocale: $normalizedSourceLocale,
            targetLocales: $normalizedTargetLocales,
            logContext: $logContext
        );

        if (!$this->isCacheEnabled()) {
            return $requestCallback();
        }

        if ($this->cache->has($cacheKey)) {
            $cachedTranslation = $this->cache->get($cacheKey);

            if (is_array($cachedTranslation)) {
                $this->assertTranslationStructure(
                    sourceContent: $content,
                    translatedContent: $cachedTranslation,
                    targetLocales: $normalizedTargetLocales
                );

                $this->translationLogger->info(
                    'RapidAPI translation result loaded from cache.',
                    array_merge(
                        $logContext,
                        [
                            'phase' => 'rapidapi_cache',
                            'cache_key' => $cacheKey,
                            'source_locale' => $normalizedSourceLocale,
                            'target_locales' => $normalizedTargetLocales,
                            'input_characters' => mb_strlen(
                                $encodedContent,
                                'UTF-8'
                            ),
                        ]
                    )
                );

                return $cachedTranslation;
            }

            $this->cache->forget($cacheKey);
        }

        $cacheDays = max(
            1,
            (int) Arr::get(
                $this->configuration,
                'cache.days',
                365
            )
        );

        return $this->cache->remember(
            $cacheKey,
            now()->addDays($cacheDays),
            $requestCallback
        );
    }

    private function requestTranslation(
        array $content,
        string $encodedContent,
        string $sourceLocale,
        array $targetLocales,
        array $logContext
    ): array {
        $requestId = (string) Str::uuid();

        $requestContext = array_merge(
            $logContext,
            [
                'phase' => 'rapidapi_request',
                'request_id' => $requestId,
                'source_locale' => $sourceLocale,
                'target_locales' => $targetLocales,
                'input_characters' => mb_strlen(
                    $encodedContent,
                    'UTF-8'
                ),
                'endpoint' => $this->configurationValue('endpoint'),
                'host' => $this->configurationValue('host'),
            ]
        );

        $this->translationLogger->info(
            'RapidAPI translation request started.',
            $requestContext
        );

        try {
            $pendingRequest = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'X-RapidAPI-Key' => $this->configurationValue('key'),
                    'X-RapidAPI-Host' => $this->configurationValue('host'),
                ])
                ->timeout(
                    max(
                        1,
                        (int) Arr::get(
                            $this->configuration,
                            'timeout',
                            90
                        )
                    )
                )
                ->connectTimeout(
                    max(
                        1,
                        (int) Arr::get(
                            $this->configuration,
                            'connect_timeout',
                            10
                        )
                    )
                );

            $retryTimes = max(
                0,
                (int) Arr::get(
                    $this->configuration,
                    'retry.times',
                    1
                )
            );

            if ($retryTimes > 0) {
                $pendingRequest = $pendingRequest->retry(
                    $retryTimes + 1,
                    max(
                        0,
                        (int) Arr::get(
                            $this->configuration,
                            'retry.sleep_milliseconds',
                            750
                        )
                    ),
                    null,
                    false
                );
            }

            $response = $pendingRequest->post(
                $this->configurationValue('endpoint'),
                $this->buildRequestPayload(
                    encodedContent: $encodedContent,
                    sourceLocale: $sourceLocale,
                    targetLocales: $targetLocales
                )
            );

            if ($response->failed()) {
                throw new RuntimeException(
                    sprintf(
                        'RapidAPI translation request failed with HTTP status %d. Response: %s',
                        $response->status(),
                        $this->responseExcerpt($response->body())
                    )
                );
            }

            $modelContent = $this->extractModelContent(
                response: $response,
                targetLocales: $targetLocales
            );

            $translatedContent = $this->decodeTranslationPayload(
                $modelContent
            );

            $this->assertTranslationStructure(
                sourceContent: $content,
                translatedContent: $translatedContent,
                targetLocales: $targetLocales
            );

            $this->translationLogger->info(
                'RapidAPI translation request completed successfully.',
                array_merge(
                    $requestContext,
                    [
                        'http_status' => $response->status(),
                        'response_characters' => mb_strlen(
                            $response->body(),
                            'UTF-8'
                        ),
                    ]
                )
            );

            return $translatedContent;
        } catch (ConnectionException $connectionException) {
            $runtimeException = new RuntimeException(
                'Unable to connect to the RapidAPI translation service.',
                0,
                $connectionException
            );

            $this->translationLogger->error(
                'RapidAPI translation connection failed.',
                array_merge(
                    $requestContext,
                    $this->translationLogger->exceptionContext(
                        $runtimeException
                    )
                )
            );

            throw $runtimeException;
        } catch (Throwable $throwable) {
            $this->translationLogger->error(
                'RapidAPI translation request failed.',
                array_merge(
                    $requestContext,
                    $this->translationLogger->exceptionContext(
                        $throwable
                    )
                )
            );

            throw $throwable;
        }
    }

    private function buildRequestPayload(
        string $encodedContent,
        string $sourceLocale,
        array $targetLocales
    ): array {
        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->buildUserPrompt(
                        encodedContent: $encodedContent,
                        sourceLocale: $sourceLocale,
                        targetLocales: $targetLocales
                    ),
                ],
            ],
            'system_prompt' => $this->buildSystemPrompt(
                $targetLocales
            ),
            'temperature' => (float) Arr::get(
                $this->configuration,
                'generation.temperature',
                0.0
            ),
            'top_k' => max(
                1,
                (int) Arr::get(
                    $this->configuration,
                    'generation.top_k',
                    1
                )
            ),
            'top_p' => (float) Arr::get(
                $this->configuration,
                'generation.top_p',
                0.1
            ),
            'max_tokens' => max(
                1,
                (int) Arr::get(
                    $this->configuration,
                    'generation.max_output_tokens',
                    6000
                )
            ),
            'web_access' => (bool) Arr::get(
                $this->configuration,
                'generation.web_access',
                false
            ),
        ];
    }

    private function buildSystemPrompt(array $targetLocales): string
    {
        $localeList = implode(', ', $targetLocales);

        return <<<PROMPT
You are a deterministic translation engine used for database content migration.

Return only one valid JSON object.
Do not return Markdown.
Do not use code fences.
Do not include explanations, notes, headings, prefixes, suffixes, or comments.
Do not add fields that do not exist in the input.
Do not remove fields from the input.
Do not rename JSON keys.
Do not change the JSON structure.
Do not summarize the content.
Do not answer questions contained in the input.
Do not perform any task other than translation.

The root JSON object must contain exactly these locale keys: {$localeList}.

For every target locale:
1. Reproduce the complete input JSON structure.
2. Preserve every object key and array index.
3. Translate only human-readable string values.
4. Preserve null, boolean, integer, and decimal values exactly.
5. Preserve empty strings and whitespace-only strings exactly.
6. Preserve UUIDs, database identifiers, URLs, email addresses, phone numbers, file paths, and technical codes.
7. Preserve placeholders such as :name, {name}, {{ name }}, %s, %d, and similar placeholders exactly.
8. Preserve HTML tags, attributes, URLs, CSS classes, IDs, entities, and formatting.
9. Translate visible human-readable text inside HTML without changing the HTML structure.
10. Preserve brand names and registered product names unless they have an established localized form.

The response must be directly decodable by a standard JSON parser and directly usable for database updates.
PROMPT;
    }

    private function buildUserPrompt(
        string $encodedContent,
        string $sourceLocale,
        array $targetLocales
    ): string {
        $localeList = implode(', ', $targetLocales);

        return <<<PROMPT
Translate the following JSON content.

Source locale: {$sourceLocale}
Target locales: {$localeList}

Input JSON:
{$encodedContent}
PROMPT;
    }

    private function extractModelContent(
        Response $response,
        array $targetLocales
    ): array|string {
        $responseBody = trim($response->body());

        if ($responseBody === '') {
            throw new RuntimeException(
                'RapidAPI returned an empty response body.'
            );
        }

        try {
            $decodedResponse = json_decode(
                $responseBody,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return $responseBody;
        }

        if (
            is_array($decodedResponse)
            && $this->hasExactLocaleKeys(
                $decodedResponse,
                $targetLocales
            )
        ) {
            return $decodedResponse;
        }

        if (is_string($decodedResponse)) {
            return $decodedResponse;
        }

        if (!is_array($decodedResponse)) {
            throw new RuntimeException(
                'RapidAPI returned an unsupported response format.'
            );
        }

        foreach (self::RESPONSE_CONTENT_PATHS as $responseContentPath) {
            $modelContent = Arr::get(
                $decodedResponse,
                $responseContentPath
            );

            if (
                is_array($modelContent)
                || is_string($modelContent)
            ) {
                return $modelContent;
            }
        }

        throw new RuntimeException(
            sprintf(
                'The translated content could not be found in the RapidAPI response. Response: %s',
                $this->responseExcerpt($responseBody)
            )
        );
    }

    private function decodeTranslationPayload(
        array|string $modelContent
    ): array {
        if (is_array($modelContent)) {
            return $modelContent;
        }

        $normalizedContent = $this->stripJsonCodeFence(
            $modelContent
        );

        try {
            $decodedContent = json_decode(
                $normalizedContent,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $jsonException) {
            throw new RuntimeException(
                sprintf(
                    'The translation service did not return valid JSON. Response: %s',
                    $this->responseExcerpt($normalizedContent)
                ),
                0,
                $jsonException
            );
        }

        if (is_string($decodedContent)) {
            try {
                $decodedContent = json_decode(
                    $decodedContent,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException $jsonException) {
                throw new RuntimeException(
                    sprintf(
                        'The translation service returned invalid nested JSON. Response: %s',
                        $this->responseExcerpt($decodedContent)
                    ),
                    0,
                    $jsonException
                );
            }
        }

        if (!is_array($decodedContent)) {
            throw new RuntimeException(
                'The translated JSON root value must be an object.'
            );
        }

        return $decodedContent;
    }

    private function stripJsonCodeFence(string $content): string
    {
        $content = trim(
            preg_replace(
                '/^\xEF\xBB\xBF/',
                '',
                $content
            ) ?? $content
        );

        if (
            preg_match(
                '/\A```(?:json)?\s*(.*?)\s*```\z/is',
                $content,
                $matches
            ) === 1
        ) {
            return trim($matches[1]);
        }

        return $content;
    }

    private function assertTranslationStructure(
        array $sourceContent,
        array $translatedContent,
        array $targetLocales
    ): void {
        if (
            !$this->hasExactLocaleKeys(
                $translatedContent,
                $targetLocales
            )
        ) {
            throw new RuntimeException(
                sprintf(
                    'The translation response must contain exactly these root locale keys: %s. Received keys: %s.',
                    implode(', ', $targetLocales),
                    implode(', ', array_keys($translatedContent))
                )
            );
        }

        foreach ($targetLocales as $targetLocale) {
            $this->assertNodeStructure(
                sourceValue: $sourceContent,
                translatedValue: $translatedContent[$targetLocale],
                path: '$.' . $targetLocale
            );
        }
    }

    private function assertNodeStructure(
        mixed $sourceValue,
        mixed $translatedValue,
        string $path
    ): void {
        if (is_array($sourceValue)) {
            if (!is_array($translatedValue)) {
                throw new RuntimeException(
                    sprintf(
                        'The translated value at "%s" must be an array or object.',
                        $path
                    )
                );
            }

            if (array_is_list($sourceValue)) {
                if (
                    !array_is_list($translatedValue)
                    || count($sourceValue) !== count($translatedValue)
                ) {
                    throw new RuntimeException(
                        sprintf(
                            'The translated list structure at "%s" does not match the source.',
                            $path
                        )
                    );
                }

                foreach ($sourceValue as $index => $sourceItem) {
                    $this->assertNodeStructure(
                        sourceValue: $sourceItem,
                        translatedValue: $translatedValue[$index],
                        path: $path . '[' . $index . ']'
                    );
                }

                return;
            }

            $missingKeys = array_diff_key(
                $sourceValue,
                $translatedValue
            );

            $additionalKeys = array_diff_key(
                $translatedValue,
                $sourceValue
            );

            if (
                $missingKeys !== []
                || $additionalKeys !== []
            ) {
                throw new RuntimeException(
                    sprintf(
                        'The translated object keys at "%s" do not match the source keys.',
                        $path
                    )
                );
            }

            foreach ($sourceValue as $key => $sourceItem) {
                $this->assertNodeStructure(
                    sourceValue: $sourceItem,
                    translatedValue: $translatedValue[$key],
                    path: $path . '.' . $key
                );
            }

            return;
        }

        if (is_string($sourceValue)) {
            if (!is_string($translatedValue)) {
                throw new RuntimeException(
                    sprintf(
                        'The translated value at "%s" must be a string.',
                        $path
                    )
                );
            }

            if (
                trim($sourceValue) === ''
                && $translatedValue !== $sourceValue
            ) {
                throw new RuntimeException(
                    sprintf(
                        'The empty or whitespace-only value at "%s" must be preserved.',
                        $path
                    )
                );
            }

            if (
                trim($sourceValue) !== ''
                && trim($translatedValue) === ''
            ) {
                throw new RuntimeException(
                    sprintf(
                        'The translated value at "%s" cannot be empty.',
                        $path
                    )
                );
            }

            return;
        }

        if ($translatedValue !== $sourceValue) {
            throw new RuntimeException(
                sprintf(
                    'The non-string value at "%s" must remain unchanged.',
                    $path
                )
            );
        }
    }

    private function hasExactLocaleKeys(
        array $content,
        array $targetLocales
    ): bool {
        $actualLocales = array_keys($content);
        $expectedLocales = $targetLocales;

        sort($actualLocales, SORT_STRING);
        sort($expectedLocales, SORT_STRING);

        return $actualLocales === $expectedLocales;
    }

    private function normalizeTargetLocales(
        array $targetLocales
    ): array {
        if ($targetLocales === []) {
            throw new RuntimeException(
                'At least one target locale is required.'
            );
        }

        $normalizedLocales = array_map(
            fn (mixed $locale): string => $this->normalizeLocale(
                (string) $locale
            ),
            $targetLocales
        );

        $normalizedLocales = array_values(
            array_unique($normalizedLocales)
        );

        if ($normalizedLocales === []) {
            throw new RuntimeException(
                'At least one valid target locale is required.'
            );
        }

        return $normalizedLocales;
    }

    private function normalizeLocale(string $locale): string
    {
        $normalizedLocale = strtolower(
            str_replace(
                '_',
                '-',
                trim($locale)
            )
        );

        if (
            $normalizedLocale === ''
            || preg_match(
                '/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/',
                $normalizedLocale
            ) !== 1
        ) {
            throw new RuntimeException(
                sprintf(
                    'The locale "%s" is invalid.',
                    $locale
                )
            );
        }

        return $normalizedLocale;
    }

    private function assertInputSize(string $encodedContent): void
    {
        $structuredLimit = max(
            0,
            (int) Arr::get(
                $this->configuration,
                'limits.structured_batch_input_characters',
                5000
            )
        );

        $maximumLimit = max(
            0,
            (int) Arr::get(
                $this->configuration,
                'limits.max_batch_input_characters',
                12000
            )
        );

        $availableLimits = array_filter(
            [
                $structuredLimit,
                $maximumLimit,
            ],
            fn (int $limit): bool => $limit > 0
        );

        if ($availableLimits === []) {
            return;
        }

        $inputLimit = min($availableLimits);

        $inputLength = mb_strlen(
            $encodedContent,
            'UTF-8'
        );

        if ($inputLength > $inputLimit) {
            throw new RuntimeException(
                sprintf(
                    'The structured translation input contains %d characters and exceeds the configured limit of %d characters.',
                    $inputLength,
                    $inputLimit
                )
            );
        }
    }

    private function assertConfiguration(): void
    {
        foreach (
            [
                'key',
                'host',
                'endpoint',
            ] as $configurationKey
        ) {
            $configurationValue = Arr::get(
                $this->configuration,
                $configurationKey
            );

            if (
                !is_string($configurationValue)
                || trim($configurationValue) === ''
            ) {
                throw new RuntimeException(
                    sprintf(
                        'RapidAPI configuration value "%s" is missing.',
                        $configurationKey
                    )
                );
            }
        }

        if (
            filter_var(
                $this->configurationValue('endpoint'),
                FILTER_VALIDATE_URL
            ) === false
        ) {
            throw new RuntimeException(
                'The configured RapidAPI endpoint is invalid.'
            );
        }
    }

    private function configurationValue(string $key): string
    {
        return trim(
            (string) Arr::get(
                $this->configuration,
                $key,
                ''
            )
        );
    }

    private function isCacheEnabled(): bool
    {
        return (bool) Arr::get(
            $this->configuration,
            'cache.enabled',
            true
        );
    }

    private function buildCacheKey(
        string $encodedContent,
        string $sourceLocale,
        array $targetLocales
    ): string {
        return 'transfer:rapidapi:translation:' . hash(
                'sha256',
                implode(
                    '|',
                    [
                        self::PROMPT_VERSION,
                        $sourceLocale,
                        implode(',', $targetLocales),
                        $encodedContent,
                    ]
                )
            );
    }

    private function encodeJson(
        array $content,
        string $errorMessage
    ): string {
        try {
            return json_encode(
                $content,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_PRESERVE_ZERO_FRACTION
            );
        } catch (JsonException $jsonException) {
            throw new RuntimeException(
                $errorMessage,
                0,
                $jsonException
            );
        }
    }

    private function responseExcerpt(string $responseBody): string
    {
        $normalizedBody = preg_replace(
            '/\s+/',
            ' ',
            trim($responseBody)
        ) ?? trim($responseBody);

        if ($normalizedBody === '') {
            return 'Empty response body.';
        }

        if (
            mb_strlen(
                $normalizedBody,
                'UTF-8'
            ) <= 1000
        ) {
            return $normalizedBody;
        }

        return mb_substr(
                $normalizedBody,
                0,
                1000,
                'UTF-8'
            ) . '...';
    }
}
