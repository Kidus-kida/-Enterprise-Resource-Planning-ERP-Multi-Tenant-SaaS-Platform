<?php

namespace Modules\Products\Listeners;

use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;

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
        $menu = $event->menu;

        // Add Products menu item
        $activeClass = route_is(["products.*"]) ? "active" : "";
        $menu
            ->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-cubes"></i><span>' . __("Products") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->add(
                        Link::toRoute('products.index', __('All Products'))->addClass(route_is(['products.index']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('products.create', __('Add Product'))->addClass(route_is(['products.create']) ? 'active' : ''),
                    )
                    ->addParentClass('submenu')
            );
    }
}
