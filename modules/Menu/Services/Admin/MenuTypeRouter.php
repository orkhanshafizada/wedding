<?php

namespace Modules\Menu\Services\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Handlers\Admin\ContactMenuAdminHandler;
use Modules\Menu\Handlers\Admin\FormMenuAdminHandler;
use Modules\Menu\Handlers\Admin\GridsMenuAdminHandler;
use Modules\Menu\Handlers\Admin\LogosPartnersMenuAdminHandler;
use Modules\Menu\Handlers\Admin\TeamStaffMenuAdminHandler;
use Modules\Menu\Handlers\ContentMenuTypeHandler;
use Modules\Menu\Handlers\CategoriesMenuTypeHandler;
use Modules\Menu\Handlers\Admin\FaqMenuAdminHandler;
use Modules\Gallery\Services\GalleryMenuAdminHandler;
use Modules\Menu\Models\Menu;

class MenuTypeRouter
{
    /**
     * @var array<string, class-string<MenuTypeHandlerInterface>>
     */
    private array $handlers;

    public function __construct(array $handlers = [])
    {
        $this->handlers = array_merge(
            [
                MenuType::CONTENT->value       => ContentMenuTypeHandler::class,
                MenuType::CATEGORIES->value    => CategoriesMenuTypeHandler::class,
                MenuType::CONTACTUS->value     => ContactMenuAdminHandler::class,
                MenuType::FAQ->value           => FaqMenuAdminHandler::class,
                MenuType::TEAMSTAFF->value     => TeamStaffMenuAdminHandler::class,
                MenuType::LOGOSPARTNERS->value => LogosPartnersMenuAdminHandler::class,
                MenuType::FORM->value          => FormMenuAdminHandler::class,
                MenuType::GRIDS->value         => GridsMenuAdminHandler::class,
                MenuType::PHOTO_GALLERY->value => GalleryMenuAdminHandler::class,
                MenuType::VIDEO_GALLERY->value => GalleryMenuAdminHandler::class,
                MenuType::FILES->value         => GalleryMenuAdminHandler::class,

            ],
            $handlers
        );
    }

    public function redirect(Menu $menu): RedirectResponse
    {
        $rawType = $menu->getAttribute('type');

        $type = is_object($rawType)
            ? (($rawType instanceof \BackedEnum)
                ? (string)$rawType->value
                : (string)($rawType->value ?? $rawType->name ?? ''))
            : (string)$rawType;

        $handlerClass = Arr::get($this->handlers, $type);

        if (!$handlerClass || !class_exists($handlerClass)) {
            throw new InvalidArgumentException('Handler not configured for type: ' . $type);
        }

        $handler = app($handlerClass);

        if (!$handler instanceof MenuTypeHandlerInterface) {
            throw new InvalidArgumentException($handlerClass . ' must implement ' . MenuTypeHandlerInterface::class);
        }

        return $handler->handle($menu);
    }
}
