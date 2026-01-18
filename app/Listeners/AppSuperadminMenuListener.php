<?php

namespace App\Listeners;

use App\Events\AppSuperadminMenuEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Menu;

class AppSuperadminMenuListener
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
    public function handle(AppSuperadminMenuEvent $event): void
    {
        $menu = $event->menu;
        
        // Back to Dashboard link
        $menu->add(
            Link::toRoute('dashboard', '<i class="la la-dashboard"></i> <span>' . __("Back to Dashboard") . '</span>')->setActive(route_is('dashboard'))
        );
        
        // Superadmin Dashboard
        $menu->add(
            Link::toRoute('superadmin.dashboard', '<i class="la la-tachometer"></i> <span>' . __("Superadmin Dashboard") . '</span>')->setActive(route_is('superadmin.dashboard'))
        );

        // Business Management Submenu
        $activeClass = route_is(['superadmin.businesses.*', 'superadmin.tenant-management.*']) ? 'active' : '';
        $menu->submenu(
            Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-building"></i><span>' . __('Business Management') . '</span><span class="menu-arrow"></span></a>'),
            Menu::new()
                ->add(
                    Link::toRoute('superadmin.businesses.index', __('All Businesses'))
                        ->addClass(route_is(['superadmin.businesses.*']) ? 'active' : '')
                )
                ->add(
                    Link::toRoute('superadmin.businesses.create', __('Add Business'))
                        ->addClass(route_is(['superadmin.businesses.create']) ? 'active' : '')
                )
                ->add(
                    Link::toRoute('superadmin.tenant-management.index', __('Tenant Management'))
                        ->addClass(route_is(['superadmin.tenant-management.*']) ? 'active' : '')
                )
                ->addParentClass('submenu')
        );
        
        // Package Management Submenu
        $activeClass = route_is(['superadmin.packages.*', 'superadmin.modules.*']) ? 'active' : '';
        $menu->submenu(
            Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-cube"></i><span>' . __('Packages & Modules') . '</span><span class="menu-arrow"></span></a>'),
            Menu::new()
                ->add(
                    Link::toRoute('superadmin.modules.index', __('All Modules'))
                        ->addClass(route_is(['superadmin.modules.*']) ? 'active' : '')
                )
                ->add(
                    Link::toRoute('superadmin.packages.index', __('All Packages'))
                        ->addClass(route_is(['superadmin.packages.index', 'superadmin.packages.show']) ? 'active' : '')
                )
                ->add(
                    Link::toRoute('superadmin.packages.create', __('Create Package'))
                        ->addClass(route_is(['superadmin.packages.create']) ? 'active' : '')
                )
                ->addParentClass('submenu')
        );
        
        // Subscription Management Submenu
        $activeClass = route_is(['superadmin.subscriptions.*']) ? 'active' : '';
        $menu->submenu(
            Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-calendar-check-o"></i><span>' . __('Subscriptions') . '</span><span class="menu-arrow"></span></a>'),
            Menu::new()
                ->add(
                    Link::toRoute('superadmin.subscriptions.index', __('All Subscriptions'))
                        ->addClass(route_is(['superadmin.subscriptions.index', 'superadmin.subscriptions.show']) ? 'active' : '')
                )
                ->add(
                    Link::toRoute('superadmin.subscriptions.create', __('Create Subscription'))
                        ->addClass(route_is(['superadmin.subscriptions.create']) ? 'active' : '')
                )
                ->addParentClass('submenu')
        );
        
        // Manual Payments
        $pendingCount = \Modules\Superadmin\Models\ManualPayment::where('status', 'pending')->count();
        $badgeHtml = $pendingCount > 0 ? '<span class="badge badge-pill bg-warning ms-auto">' . $pendingCount . '</span>' : '';
        $menu->add(
            Link::toRoute('superadmin.manual-payments.index', '<i class="la la-money"></i> <span>' . __('Manual Payments') . '</span>' . $badgeHtml)
                ->setActive(route_is(['superadmin.manual-payments.*']))
        );
        
        // Superadmin Settings
        $menu->add(
            Link::toRoute('superadmin.settings.index', '<i class="la la-cog"></i> <span>' . __('Superadmin Settings') . '</span>')
                ->setActive(route_is(['superadmin.settings.*']))
        );
    }
}
