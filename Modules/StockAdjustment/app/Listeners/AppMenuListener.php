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
        if (request()->routeIs('tenant.dashboard')) {
            return;
        }

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
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-ship"></i><span>Stock-Adjustment</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                 ->add(
                        Link::toRoute('stock_adjustment.index', 'List Stock Adjustment')
                            ->addClass(route_is(['stock_adjustment.index.*']) ? 'active' : ''),
                    )
                    
                    ->add(
                        Link::toRoute('stockadjustment-settings.create', 'Stock Adjustment Settings')
                            ->addClass(route_is(['stockadjustment-settings.create.*']) ? 'active' : ''),
                    )
                   
                    
                   
                    
                    ->addParentClass('submenu')
            );
        }
}
