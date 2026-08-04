<?php

namespace Modules\Menu\Services;

use Illuminate\Contracts\Container\Container;
use LogicException;
use Modules\FAQ\Handlers\Api\FaqMenuApiHandler;
use Modules\Form\Handlers\Api\FormMenuApiHandler;
use Modules\Gallery\Handlers\Api\GalleryMenuApiHandler;
use Modules\Grids\Handlers\Api\GridsMenuApiHandler;
use Modules\LogosPartners\Handlers\Api\LogosPartnersMenuApiHandler;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Handlers\Api\ContentMenuApiHandler;
use Modules\Menu\Models\Menu;
use Modules\TeamStaff\Handlers\Api\TeamStaffMenuApiHandler;

class MenuApiDetailResolver
{
    /**
     * @var array<string, class-string<MenuTypeApiHandler>>
     */
    protected array $typeToHandlerMap = [
        MenuType::CONTENT->value       => ContentMenuApiHandler::class,
        MenuType::GRIDS->value         => GridsMenuApiHandler::class,
        MenuType::LOGOSPARTNERS->value => LogosPartnersMenuApiHandler::class,
        MenuType::FAQ->value           => FaqMenuApiHandler::class,
        MenuType::TEAMSTAFF->value     => TeamStaffMenuApiHandler::class,
        MenuType::FORM->value          => FormMenuApiHandler::class,
        MenuType::PHOTO_GALLERY->value => GalleryMenuApiHandler::class,
        MenuType::VIDEO_GALLERY->value => GalleryMenuApiHandler::class,
        MenuType::FILES->value         => GalleryMenuApiHandler::class,
    ];

    public function __construct(
        protected readonly Container $container
    ) {}

    public function resolve(Menu $menu): MenuTypeApiHandler
    {
        $type = $menu->type instanceof MenuType ? $menu->type->value : (string)$menu->type;

        $handlerClass = $this->typeToHandlerMap[$type] ?? null;

        if ($handlerClass === null) {
            throw new LogicException(sprintf('No api handler registered for menu type "%s".', $type));
        }

        $handler = $this->container->make($handlerClass);

        if (!$handler instanceof MenuTypeApiHandler) {
            throw new LogicException(sprintf(
                'Handler class "%s" must implement %s.',
                $handlerClass,
                MenuTypeApiHandler::class
            ));
        }

        return $handler;
    }

    public function handle(Menu $menu, MenuDetailContext $context): mixed
    {
        $handler = $this->resolve($menu);

        return $handler->handle($menu, $context);
    }
}
