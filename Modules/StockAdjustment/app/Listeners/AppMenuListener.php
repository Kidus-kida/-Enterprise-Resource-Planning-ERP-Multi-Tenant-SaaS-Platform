<?php

namespace Modules\StockAdjustment\Listeners;

use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Menu;
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
       $menu = $event->menu;
        
        // Check if user is authenticated
        if(!auth()->check()) {
            return;
        }

        // Determine active state
        $activeClass = route_is([
            "stockadjustment.*"
        ]) ? "active" : "";

        // Add Stock Adjustment Module Menu
        $menu
            ->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-ship"></i><span>' . __("Stock-Adjustment") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->add(
                        Link::toRoute('stockadjustment.index', __('List Stock Adjustments'))
                            ->addClass(route_is(['stockadjustment.index.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('stockadjustment.index', __('Add Stock Adjustment'))
                            ->addClass(route_is(['stockadjustment.index.*']) ? 'active' : ''),
                    )
                    ->add(
                        Link::toRoute('stockadjustment.index', __('Stock Adjustment Settings'))
                            ->addClass(route_is(['stockadjustment.index.*']) ? 'active' : ''),
                    )
                    
                    ->addParentClass('submenu')
            );
        }
}
