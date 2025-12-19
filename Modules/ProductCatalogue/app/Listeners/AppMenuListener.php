<?php

namespace Modules\ProductCatalogue\Listeners;

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

        // Add ProductCatalogue menu item
        $activeClass = route_is(["product-catalogue.*", "catalogue.*"]) ? "active" : "";
        $menu
            ->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-qrcode"></i><span>' . __("Product Catalogue") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->add(
                        Link::toUrl(route('product-catalogue.catalogue-qr'), __('Generate QR Code'))->addClass(route_is(['product-catalogue.catalogue-qr']) ? 'active' : ''),
                    )
                    ->addParentClass('submenu')
            );
    }
}
