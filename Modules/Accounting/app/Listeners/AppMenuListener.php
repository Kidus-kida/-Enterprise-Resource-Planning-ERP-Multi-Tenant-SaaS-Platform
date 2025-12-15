<?php

namespace Modules\Accounting\Listeners;

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
        
        // // Check if user is authenticated
        // if(!auth()->check()) {
        //     return;
        // }

        // Determine active state
        // $activeClass = route_is([
        //     "account.*",
        //     "journal.*",
        //     "accounting.*",
        //     "fixed-asset.*",
        //     "post-dated-cheques.*",
        //     "pdc.*",
        //     "account-settings.*",
        //     "account-types.*",
        //     "account-groups.*",
        //     "budgets.*",
        //     "budget.*"
        // ]) ? "active" : "";

        // // Add Accounting Module Menu
        // $menu
        //     ->submenu(
        //         Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-calculator"></i><span>' . __("Accounting") . '</span><span class="menu-arrow"></span></a>'),
        //         Menu::new()
        //             // Chart of Accounts
        //             ->add(
        //                 Link::toRoute('account.index', __('Chart of Accounts'))
        //                     ->addClass(route_is(['account.index', 'account.show']) ? 'active' : ''),
        //             )
                    
        //             // Journals
        //             ->add(
        //                 Link::toRoute('journal.index', __('Journals'))
        //                     ->addClass(route_is(['journal.*']) ? 'active' : ''),
        //             )
                    
        //             // Financial Reports Submenu
        //             ->submenu(
        //                 Html::raw('<a href="#"><span>' . __('Financial Reports') . '</span><span class="menu-arrow"></span></a>'),
        //                 Menu::new()
        //                     ->add(
        //                         Link::toRoute('accounting.income-statement', __('Income Statement'))
        //                             ->addClass(route_is(['accounting.income-statement']) ? 'active' : ''),
        //                     )
        //                     ->add(
        //                         Link::toRoute('accounting.balance-sheet', __('Balance Sheet'))
        //                             ->addClass(route_is(['accounting.balance-sheet*']) ? 'active' : ''),
        //                     )
        //                     ->add(
        //                         Link::toRoute('accounting.trial-balance', __('Trial Balance'))
        //                             ->addClass(route_is(['accounting.trial-balance*']) ? 'active' : ''),
        //                     )
        //                     ->add(
        //                         Link::toRoute('accounting.cash-flow', __('Cash Flow'))
        //                             ->addClass(route_is(['accounting.cash-flow']) ? 'active' : ''),
        //                     )
        //                     ->add(
        //                         Link::toRoute('accounting.payment-account-report', __('Payment Account Report'))
        //                             ->addClass(route_is(['accounting.payment-account-report']) ? 'active' : ''),
        //                     )
        //                     ->addParentClass('submenu')
        //             )
                    
        //             // Fixed Assets
        //             ->add(
        //                 Link::toRoute('fixed-asset.index', __('Fixed Assets'))
        //                     ->addClass(route_is(['fixed-asset.*']) ? 'active' : ''),
        //             )
                    
        //             // Post-Dated Cheques
        //             ->add(
        //                 Link::toRoute('post-dated-cheques.index', __('Post-Dated Cheques'))
        //                     ->addClass(route_is(['post-dated-cheques.*', 'pdc.*']) ? 'active' : ''),
        //             )
                    
        //             // Settings Submenu
        //             ->submenu(
        //                 Html::raw('<a href="#"><span>' . __('Settings') . '</span><span class="menu-arrow"></span></a>'),
        //                 Menu::new()
        //                     ->add(
        //                         Link::toRoute('account-types.index', __('Account Types'))
        //                             ->addClass(route_is(['account-types.*']) ? 'active' : ''),
        //                     )
        //                     ->add(
        //                         Link::toRoute('account-groups.index', __('Account Groups'))
        //                             ->addClass(route_is(['account-groups.*']) ? 'active' : ''),
        //                     )
        //                     ->add(
        //                         Link::toRoute('account-settings.index', __('Account Settings'))
        //                             ->addClass(route_is(['account-settings.*']) ? 'active' : ''),
        //                     )
        //                     ->addParentClass('submenu')
        //             )
        //             ->addParentClass('submenu')
        //     );
   
   
   
        }
}
