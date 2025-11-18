<?php

namespace Modules\Crm\Listeners;

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
        if(auth()->user()->canAny(['view-budgetCategories','view-budgets','view-budgetExpenses','view-budgetRevenues'])){
            $menu = $event->menu;
            $activeClass = route_is(["campaigns.*","leads.*","follow-ups.*","crm-reports.*"]) ? "active" : "";
            $menu
                ->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-files-o"></i><span> ' . __("Crm") . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->add(Link::toRoute('leads.index', __('Leads'))->addClass(route_is(['leads.*']) ? 'active' : ''))
                        ->add(Link::toRoute('follow-ups.index', __('Follow-ups'))->addClass(route_is(['follow-ups.*']) ? 'active' : ''))
                        ->addIfCan('view-budgetCategories',
                            Link::toRoute('campaigns.index', __('Campaigns'))->addClass(route_is(['campaigns.*']) ? 'active' : ''),
                        )
                        ->add(Link::toRoute('crm-reports.index', __('Report'))->addClass(route_is(['crm-reports.*']) ? 'active' : ''))
                        ->addParentClass('submenu')
                );
        }
    }
}
