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
        if (request()->routeIs('tenant.dashboard')) {
            return;
        }

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
                    ->add(
                        Link::toRoute('products.variations.index', __('Variations'))->addClass(route_is(['products.variations.index']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('products.brands.index', __('Brands'))->addClass(route_is(['products.brands.index']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('products.categories.index', __('Categories'))->addClass(route_is(['products.categories.index']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('products.units.index', __('Units'))->addClass(route_is(['products.units.index']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('products.selling-price-group.index', __('Selling Price Groups'))->addClass(route_is(['products.selling-price-group.index']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('products.merged-sub-categories.index', __('Merged Sub Categories'))->addClass(route_is(['products.merged-sub-categories.index']) ? 'active' : ''),
                    )
                    ->addParentClass('submenu')
            );
    }
}
