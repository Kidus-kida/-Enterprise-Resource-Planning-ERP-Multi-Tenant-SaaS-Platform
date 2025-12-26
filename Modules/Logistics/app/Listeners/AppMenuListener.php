<?php

namespace Modules\Logistics\Listeners;

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
        
        // Check if user is authenticated
        if(!auth()->check()) {
            return;
        }

        // Determine active state
        $activeClass = route_is([
            "logistics.*"
        ]) ? "active" : "";

        // Add Logistics Module Menu
        $menu
            ->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-ship"></i><span>' . __("Logistics") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->add(
                        Link::toRoute('logistics.dashboard', __('Dashboard'))
                            ->addClass(route_is(['logistics.dashboard']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.shipments.index', __('Shipments'))
                            ->addClass(route_is(['logistics.shipments.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.containers.index', __('Containers'))
                            ->addClass(route_is(['logistics.containers.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.customs.index', __('Customs'))
                            ->addClass(route_is(['logistics.customs.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.transport.index', __('Transport'))
                            ->addClass(route_is(['logistics.transport.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.documents.index', __('Documents'))
                            ->addClass(route_is(['logistics.documents.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.calculator.index', __('Duty Calculator'))
                            ->addClass(route_is(['logistics.calculator.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('logistics.reports.index', __('Reports'))
                            ->addClass(route_is(['logistics.reports.*']) ? 'active' : ''),
                    )
                    ->addParentClass('submenu')
            );
    }
}
