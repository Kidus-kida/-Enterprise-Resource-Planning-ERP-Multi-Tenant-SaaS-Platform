<?php

namespace Modules\Roles\Listeners;

use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Link;


class AppMenuListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppMenuEvent $event): void
    {
        // Roles & Permissions menu is now managed in the main AppMenuListener
        // under the Administration submenu. This prevents duplicate menu items.
        // 
        // If you need to add this back as a standalone item, uncomment below:
        // $menu = $event->menu;
        // $menu->addIfCan('view-roles', Link::toRoute('roles.index', '<i class="la la-key"></i> <span>' . __('Roles & Permissions') . '</span>')->setActive(route_is('roles.*') ? 'active' : ''));
    }
}
