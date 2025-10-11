<?php

namespace Modules\ToolCRM\Listeners;

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
        $menu->html('<span>CRM Tools</span>', ['class' => 'menu-title']);
        if(auth()->user()->canAny(['view-budgetCategories','view-budgets','view-budgetExpenses','view-budgetRevenues'])){
            $activeClass = route_is(["budget.categories.*","budgets.*","budget.expenses.*","budget.revenue.*"]) ? "active" : "";
            $menu
                ->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-files-o"></i><span> ' . __("CRM") . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addIfCan('view-budgetCategories',
                            Link::toRoute('budget.categories.index', __('Leads'))->addClass(route_is(['budget.categories.*']) ? 'active' : ''),
                        )
                        ->addIfCan('view-budgets',
                            Link::toRoute('budgets.index', __('Follow up'))->addClass(route_is(['budgets.*']) ? 'active' : ''),
                        )
                        ->addIfCan('view-budgetExpenses',
                            Link::toRoute('budget.expense.index', __('Reports'))->addClass(route_is(['budget.expense.*']) ? 'active' : ''),
                        )
                        ->addParentClass('submenu')
                );
        }
    }
}
