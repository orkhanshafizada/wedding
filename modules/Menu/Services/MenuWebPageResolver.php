<?php

namespace Modules\Menu\Services;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\View\View;
use LogicException;
use Modules\Gallery\Handlers\Admin\GalleryMenuAdminHandler;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Handlers\Admin\GridsMenuAdminHandler;
use Modules\Menu\Handlers\Admin\LogosPartnersMenuAdminHandler;
use Modules\Menu\Handlers\Admin\TeamStaffMenuAdminHandler;
use Modules\Menu\Handlers\FaqMenuTypeHandler;
use Modules\Menu\Handlers\Web\CategoriesMenuWebHandler;
use Modules\Menu\Handlers\Web\ContactMenuWebHandler;
use Modules\Menu\Handlers\Web\ContentMenuWebHandler;
use Modules\Menu\Handlers\Web\FormMenuWebHandler;
use Modules\Menu\Handlers\Web\LinkMenuWebHandler;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class MenuWebPageResolver
{
    protected array $typeToHandlerMap = [
        MenuType::CONTENT->value => ContentMenuWebHandler::class,
        MenuType::CATEGORIES->value => CategoriesMenuWebHandler::class,
        MenuType::CONTACTUS->value => ContactMenuWebHandler::class,
        MenuType::LINK->value => LinkMenuWebHandler::class,
        MenuType::FAQ->value => FaqMenuTypeHandler::class,
        MenuType::TEAMSTAFF->value => TeamStaffMenuAdminHandler::class,
        MenuType::LOGOSPARTNERS->value => LogosPartnersMenuAdminHandler::class,
        MenuType::FORM->value => FormMenuWebHandler::class,
        MenuType::GRIDS->value => GridsMenuAdminHandler::class,
        MenuType::PHOTO_GALLERY->value => GalleryMenuAdminHandler::class,
        MenuType::VIDEO_GALLERY->value => GalleryMenuAdminHandler::class,
        MenuType::FILES->value => GalleryMenuAdminHandler::class,
    ];

    public function __construct(
        protected readonly Container $container
    ) {
    }

    public function handle(Menu $menu): Response|View
    {
        abort_unless((int) $menu->status === 1, Response::HTTP_NOT_FOUND);

        $handler = $this->getHandlerForMenu($menu);

        return $handler->handle($menu);
    }

    public function handleByLink(string $link): Response|View
    {
        $normalizedLink = $this->normalizeLink($link);

        if ($this->isReservedPath($normalizedLink)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $linkWithoutLeadingSlash = ltrim($normalizedLink, '/');

        $menu = Menu::query()
            ->with([
                'translations',
                'content',
            ])
            ->where('status', 1)
            ->whereHas('translations', function ($query) use (
                $normalizedLink,
                $linkWithoutLeadingSlash
            ): void {
                $query->whereIn('link', [
                    $normalizedLink,
                    $linkWithoutLeadingSlash,
                ]);
            })
            ->firstOrFail();

        return $this->handle($menu);
    }

    protected function getHandlerForMenu(Menu $menu): MenuTypeWebHandler
    {
        $type = $menu->type;

        $typeValue = $type instanceof MenuType
            ? $type->value
            : (string) $type;

        $handlerClass = $this->typeToHandlerMap[$typeValue] ?? null;

        if ($handlerClass === null) {
            throw new LogicException(
                sprintf(
                    'No web handler registered for menu type "%s".',
                    $typeValue
                )
            );
        }

        $handler = $this->container->make($handlerClass);

        if (! $handler instanceof MenuTypeWebHandler) {
            throw new LogicException(
                sprintf(
                    'Handler class "%s" must implement %s.',
                    $handlerClass,
                    MenuTypeWebHandler::class
                )
            );
        }

        return $handler;
    }

    protected function normalizeLink(string $link): string
    {
        $link = trim($link);

        if ($link === '') {
            return '/';
        }

        $parsedPath = parse_url($link, PHP_URL_PATH);

        if (is_string($parsedPath) && $parsedPath !== '') {
            $link = $parsedPath;
        }

        $link = '/' . ltrim($link, '/');

        if ($link !== '/') {
            $link = rtrim($link, '/');
        }

        return $link;
    }

    protected function isReservedPath(string $link): bool
    {
        $path = ltrim($link, '/');

        if ($path === '') {
            return false;
        }

        $reservedPrefixes = [
            'admin',
            'ayti',
            'api',
            'assets',
            'build',
            'css',
            'fonts',
            'images',
            'js',
            'storage',
            'vendor',
        ];

        foreach ($reservedPrefixes as $reservedPrefix) {
            if (
                $path === $reservedPrefix
                || str_starts_with($path, $reservedPrefix . '/')
            ) {
                return true;
            }
        }

        return $this->isStaticFilePath($path);
    }

    protected function isStaticFilePath(string $path): bool
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === '') {
            return false;
        }

        return in_array($extension, [
            'avif',
            'css',
            'eot',
            'gif',
            'ico',
            'jpeg',
            'jpg',
            'js',
            'json',
            'map',
            'mp3',
            'mp4',
            'ogg',
            'pdf',
            'png',
            'svg',
            'ttf',
            'webm',
            'webp',
            'woff',
            'woff2',
            'xml',
        ], true);
    }
}