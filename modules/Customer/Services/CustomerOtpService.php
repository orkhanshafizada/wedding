<?php

namespace Modules\Customer\Services;

use App\Mail\DynamicTemplateMail;
use App\Services\Mail\SettingsMailService;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerOtpCode;
use Modules\Notification\Services\NotificationTemplateRenderService;

class CustomerOtpService
{
    public function __construct(
        private readonly SettingsMailService $mailService,
        private readonly NotificationTemplateRenderService $templateRenderService,
    ) {
    }

    public function generateOtp(Customer $customer, string $type, int $ttlSeconds = 300): CustomerOtpCode
    {
        return DB::transaction(function () use ($customer, $type, $ttlSeconds): CustomerOtpCode {
            CustomerOtpCode::query()
                ->where('customer_id', $customer->id)
                ->forType($type)
                ->whereNull('used_at')
                ->delete();

            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            return CustomerOtpCode::create([
                'customer_id' => $customer->id,
                'type' => $type,
                'code' => $code,
                'expires_at' => now()->addSeconds($ttlSeconds),
                'used_at' => null,
                'attempts' => 0,
            ]);
        });
    }

    public function generateAndSendForEmailVerification(Customer $customer, ?string $verifyLink = null): CustomerOtpCode
    {
        $otp = $this->generateOtp($customer, CustomerOtpCode::TYPE_EMAIL_VERIFICATION);

        $this->sendTemplateMail(
            $customer,
            'email_confirm',
            [
                'customer_name' => $customer->name,
                'customer_surname' => (string) ($customer->surname ?? ''),
                'customer_email' => $customer->email,
                'code' => $otp->code,
                'link' => (string) ($verifyLink ?? ''),
                'logo_light' => $this->resolveSettingImageUrl('logo'),
                'logo_dark' => $this->resolveSettingImageUrl('logo_dark'),
            ]
        );

        return $otp;
    }

    public function generateAndSendForPasswordReset(Customer $customer, ?string $resetLink = null): CustomerOtpCode
    {
        $otp = $this->generateOtp($customer, CustomerOtpCode::TYPE_PASSWORD_RESET);

        $this->sendTemplateMail(
            $customer,
            'forgot_password',
            [
                'customer_name' => $customer->name,
                'customer_surname' => (string) ($customer->surname ?? ''),
                'customer_email' => $customer->email,
                'code' => $otp->code,
                'link' => (string) ($resetLink ?? ''),
                'logo_light' => $this->resolveSettingImageUrl('logo'),
                'logo_dark' => $this->resolveSettingImageUrl('logo_dark'),
            ]
        );

        return $otp;
    }

    public function sendWelcome(Customer $customer): void
    {
        $this->sendTemplateMail(
            $customer,
            'welcome',
            [
                'customer_name' => $customer->name,
                'customer_surname' => (string) ($customer->surname ?? ''),
                'customer_email' => $customer->email,
                'logo_light' => $this->resolveSettingImageUrl('logo'),
                'logo_dark' => $this->resolveSettingImageUrl('logo_dark'),
            ]
        );
    }

    public function verify(Customer $customer, string $type, string $code): array
    {
        return DB::transaction(function () use ($customer, $type, $code): array {
            $normalizedCode = preg_replace('/\s+/', '', trim($code)) ?? '';

            $otp = CustomerOtpCode::query()
                ->where('customer_id', $customer->id)
                ->forType($type)
                ->whereNull('used_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$otp) {
                return ['success' => false, 'reason' => 'not_found', 'otp' => null];
            }

            if ($otp->isExpired()) {
                return ['success' => false, 'reason' => 'expired', 'otp' => $otp];
            }

            if ($otp->hasTooManyAttempts()) {
                return ['success' => false, 'reason' => 'too_many_attempts', 'otp' => $otp];
            }

            if (!preg_match('/^[0-9]{4}$/', $normalizedCode)) {
                $otp->incrementAttempts();

                return ['success' => false, 'reason' => 'invalid_code', 'otp' => $otp->fresh()];
            }

            if (!hash_equals((string) $otp->code, $normalizedCode)) {
                $otp->incrementAttempts();

                return ['success' => false, 'reason' => 'invalid_code', 'otp' => $otp->fresh()];
            }

            $otp->markUsed();

            return ['success' => true, 'reason' => null, 'otp' => $otp->fresh()];
        });
    }

    private function sendTemplateMail(Customer $customer, string $templateKey, array $variables): void
    {
        $rendered = $this->templateRenderService->renderEmail($templateKey, $variables);

        $subject = trim((string) ($rendered['subject'] ?? ''));
        $body = trim((string) ($rendered['body'] ?? ''));

        if ($subject === '' && $body === '') {
            return;
        }

        $this->mailService->sendTo(
            $customer->email,
            new DynamicTemplateMail($subject !== '' ? $subject : config('app.name'), $body)
        );
    }

    private function resolveSettingImageUrl(?string $key): string
    {
        $generalImages = Settings::get('general', 'images', []);
        $path = $generalImages[$key] ?? null;

        if (!is_string($path) || $path === '') {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
