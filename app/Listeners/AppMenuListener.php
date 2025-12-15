<?php

namespace App\Listeners;

use App\Enums\UserType;
use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\Menu\Laravel\Html;

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

        // ==================== DASHBOARD ====================
        $menu->add(
            Link::toRoute('dashboard', '<i class="la la-dashboard"></i> <span>' . __('Dashboard') . '</span>')->setActive(route_is('dashboard'))
        );

        // ==================== HR MANAGEMENT ====================
        if (
            auth()->user()->canAny([
                'view-employees',
                'view-attendances',
                'view-departments',
                'view-designations',
                'view-request',
                'edit-request',
                'create-annual-leave',
                'create-leave-type',
                'view-award',
                'view-evaluation',
                'view-evaluation-assignment',
                'view-PayrollAllowances',
                'view-PayrollDeductions',
                'view-payrolls',
                'view-payslips'
            ])
        ) {
            $menu->html('<span>HR</span>', ['class' => 'menu-title']);

            // Employees Submenu
            if (auth()->user()->canAny(['view-employees', 'view-attendances', 'view-departments', 'view-designations'])) {
                $activeClass = route_is(['employees.index', 'employees.list', 'departments.index', 'designations.index', 'attendances.index']) ? "active" : "";
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-users"></i> <span>' . __('Employees') . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addParentClass('submenu')
                        ->addIfCan('view-employees', Link::toRoute('employees.index', __('All Employees'))->addClass(route_is(['employees.index', 'employees.list']) ? 'active' : ''))
                        ->addIfCan('view-departments', Link::toRoute('departments.index', __('Departments'))->addClass(route_is('departments.index') ? 'active' : ''))
                        ->addIfCan('view-designations', Link::toRoute('designations.index', __('Designations'))->addClass(route_is('designations.index') ? 'active' : ''))
                        ->addIfCan('view-attendances', Link::toRoute('attendances.index', __('Attendance'))->addClass(route_is(['attendances.index']) ? 'active' : ''))
                );
            }

            // Leave Management Submenu
            if (auth()->user()->canAny(['view-request', 'edit-request', 'create-annual-leave', 'create-leave-type'])) {
                $activeClass = route_is(['leaverequests.index', 'leaverequests.myleaverequests', 'leavetypes.index', 'annual_leaves.index']) ? "active" : "";
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-calendar-check-o"></i> <span>' . __('Leave Management') . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addParentClass('submenu')
                        ->addIfCan('edit-request', Link::toRoute('leaverequests.index', __('Leave Requests'))->addClass(route_is(['leaverequests.index']) ? 'active' : ''))
                        ->addIfCan('view-request', Link::toRoute('leaverequests.myleaverequests', __('My Leaves'))->addClass(route_is(['leaverequests.myleaverequests']) ? 'active' : ''))
                        ->addIfCan('create-leave-type', Link::toRoute('leavetypes.index', __('Leave Types'))->addClass(route_is(['leavetypes.index']) ? 'active' : ''))
                        ->addIfCan('create-annual-leave', Link::toRoute('annual_leaves.index', __('Annual Leave Settings'))->addClass(route_is(['annual_leaves.index']) ? 'active' : ''))
                );
            }

            // Performance Submenu
            if (auth()->user()->canAny(['view-evaluation', 'view-evaluation-assignment', 'view-award'])) {
                $activeClass = route_is(['evaluation.index', 'evaluation.assign-evaluator', 'awards.*']) ? "active" : "";
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-chart-line"></i> <span>' . __('Performance') . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addParentClass('submenu')
                        ->addIfCan('view-evaluation', Link::toRoute('evaluation.index', __('Evaluations'))->addClass(route_is('evaluation.index') ? 'active' : ''))
                        ->addIfCan('view-evaluation-assignment', Link::toRoute('evaluation.assign-evaluator', __('Evaluation Assignments'))->addClass(route_is('evaluation.assign-evaluator') ? 'active' : ''))
                        ->addIfCan('view-award', Link::toRoute('awards.index', __('Awards & Recognition'))->addClass(route_is('awards.*') ? 'active' : ''))
                );
            }

            // Payroll Submenu
            if (auth()->user()->canAny(['view-PayrollAllowances', 'view-PayrollDeductions', 'view-payrolls', 'view-payslips'])) {
                $payrollActive = route_is(['payroll.*', 'payslips.*', 'allowances.*', 'deductions.*']);
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $payrollActive . '"><i class="la la-money"></i><span>' . __("Payroll") . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addIf(
                            function () {
                                return auth()->user()->can(['view-PayrollAllowances', 'view-PayrollDeductions']);
                            },
                            Link::toRoute('payroll.items', __('Payroll Items'))->addClass(route_is(['payroll.items']) ? 'active' : '')
                        )
                        ->addIfCan("view-payrolls", Link::toRoute('payroll.processing.index', __('Payroll Processing'))->addClass(route_is(['payroll.processing.*']) ? 'active' : ''))
                        ->addIfCan("view-payslips", Link::toRoute('payslips.index', __('Payslips'))->addClass(route_is(['payslips.*']) ? 'active' : ''))
                        ->addParentClass('submenu')
                );
            }
        }



        // ==================== BUSINESS ====================
        $menu->html('<span>Business</span>', ['class' => 'menu-title']);
        
        // Projects
        if (auth()->user()->canAny(['view-projects', 'view-taskboards'])) {
            $activeClass = route_is(["projects.*", "task-boards.*"]) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-rocket"></i><span>' . __("Projects") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addIfCan('view-projects', Link::toRoute('projects.index', __('Projects'))->addClass(route_is(['projects.*']) ? 'active' : ''))
                    ->addIfCan('view-taskboards', Link::toRoute('task-boards.index', __('Default TaskBoards'))->addClass(route_is(['task-boards.index']) ? 'active' : ''))
                    ->addParentClass('submenu')
            );
        }

        // CRM
        if (auth()->user()->canAny(['view-clients', 'view-budgetCategories', 'view-budgets', 'view-budgetExpenses', 'view-budgetRevenues'])) {
            $activeClass = route_is(['clients.*', 'campaigns.*', 'leads.*', 'follow-ups.*', 'crm-reports.*']) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-handshake-o"></i> <span>' . __('CRM') . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addParentClass('submenu')
                    ->addIfCan('view-clients', Link::toRoute('clients.index', __('Clients'))->addClass(route_is('clients.*') ? 'active' : ''))
                    ->add(Link::toRoute('leads.index', __('Leads'))->addClass(route_is(['leads.*']) ? 'active' : ''))
                    ->add(Link::toRoute('follow-ups.index', __('Follow-ups'))->addClass(route_is(['follow-ups.*']) ? 'active' : ''))
                    ->addIfCan('view-budgetCategories', Link::toRoute('campaigns.index', __('Campaigns'))->addClass(route_is(['campaigns.*']) ? 'active' : ''))
                    ->add(Link::toRoute('crm-reports.index', __('Report'))->addClass(route_is(['crm-reports.*']) ? 'active' : ''))
            );
        }

        // Sales
        if (auth()->user()->canAny(['view-taxes', 'view-expenses', 'view-estimates', 'view-invoices'])) {
            $activeClass = route_is(["taxes.*", "expenses.*", "estimates.*", "invoices.*"]) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-shopping-cart"></i><span>' . __("Sales") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addIfCan('view-taxes', Link::toRoute('taxes.index', __('Taxes'))->addClass(route_is(['taxes.*']) ? 'active' : ''))
                    ->addIfCan('view-expenses', Link::toRoute('expenses.index', __('Expenses'))->addClass(route_is(['expenses.*']) ? 'active' : ''))
                    ->addIfCan('view-estimates', Link::toRoute('estimates.index', __('Estimates'))->addClass(route_is(['estimates.*']) ? 'active' : ''))
                    ->addIfCan('view-invoices', Link::toRoute('invoices.index', __('Invoices'))->addClass(route_is(['invoices.*']) ? 'active' : ''))
                    ->addParentClass('submenu')
            );
        }

        // Accounting
        if (auth()->user()->canAny([
            'view-budgetCategories',
            'view-budgets',
            'view-budgetExpenses',
            'view-budgetRevenues',
            // Add any other permission that should grant access to the full Accounting menu
            // e.g., 'view-accounts', 'view-journals', etc., if needed
        ])) {
            // Determine if any accounting-related route is active — broader match
            $isAccountingActive = route_is([
                'budget.categories.*',
                'budgets.*',
                'budget.expense.*',
                'budget.revenue.*',
                'account.*',
                'journal.*',
                'accounting.income-statement',
                'accounting.balance-sheet*',
                'accounting.trial-balance*',
                'accounting.cash-flow',
                'accounting.payment-account-report',
                'fixed-asset.*',
                'post-dated-cheques.*',
                'pdc.*',
                'account-types.*',
                'account-groups.*',
                'account-settings.*'
            ]);

            $menu->submenu(
                Html::raw('<a href="#" class="' . ($isAccountingActive ? 'active' : '') . '"><i class="la la-calculator"></i><span>' . __("Accounting") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()

                    // Core accounting items (adjust permissions as needed — currently no gate; add if required)
                    ->add(Link::toRoute('account.index', __('Accounts'))
                        ->addClass(route_is(['account.index', 'account.show']) ? 'active' : ''))
                    ->add(Link::toRoute('journal.index', __('Journals'))
                        ->addClass(route_is(['journal.*']) ? 'active' : ''))

                    // Financial Reports Submenu
                    ->submenu(
                        Html::raw('<a href="#"><span>' . __('Financial Reports') . '</span><span class="menu-arrow"></span></a>'),
                        Menu::new()
                            ->add(Link::toRoute('accounting.income-statement', __('Income Statement'))
                                ->addClass(route_is(['accounting.income-statement']) ? 'active' : ''))
                            ->add(Link::toRoute('accounting.balance-sheet', __('Balance Sheet'))
                                ->addClass(route_is(['accounting.balance-sheet*']) ? 'active' : ''))
                            ->add(Link::toRoute('accounting.trial-balance', __('Trial Balance'))
                                ->addClass(route_is(['accounting.trial-balance*']) ? 'active' : ''))
                            ->add(Link::toRoute('accounting.cash-flow', __('Cash Flow'))
                                ->addClass(route_is(['accounting.cash-flow']) ? 'active' : ''))
                            ->add(Link::toRoute('accounting.payment-account-report', __('Payment Account Report'))
                                ->addClass(route_is(['accounting.payment-account-report']) ? 'active' : ''))
                            ->addParentClass('submenu')
                    )

                    // Fixed Assets
                    ->add(Link::toRoute('fixed-asset.index', __('Fixed Assets'))
                        ->addClass(route_is(['fixed-asset.*']) ? 'active' : ''))

                    // Post-Dated Cheques
                    ->add(Link::toRoute('post-dated-cheques.index', __('Post-Dated Cheques'))
                        ->addClass(route_is(['post-dated-cheques.*', 'pdc.*']) ? 'active' : ''))

                    // Settings Submenu
                    // ->submenu(
                    //     Html::raw('<a href="#"><span>' . __('Settings') . '</span><span class="menu-arrow"></span></a>'),
                    //     Menu::new()
                    //         ->add(Link::toRoute('account-types.index', __('Account Types'))
                    //             ->addClass(route_is(['account-types.*']) ? 'active' : ''))
                    //         ->add(Link::toRoute('account-groups.index', __('Account Groups'))
                    //             ->addClass(route_is(['account-groups.*']) ? 'active' : ''))
                    //         ->add(Link::toRoute('account-settings.index', __('Account Settings'))
                    //             ->addClass(route_is(['account-settings.*']) ? 'active' : ''))
                    //         ->addParentClass('submenu')
                    // )

                    // Budgets Submenu
                    ->submenu(
                        Html::raw('<a href="#"><span>' . __('Budgets') . '</span><span class="menu-arrow"></span></a>'),
                        Menu::new()
                            ->addIfCan('view-budgets', Link::toRoute('budgets.index', __('Budgets'))
                                ->addClass(route_is(['budgets.*']) ? 'active' : ''))
                            ->addIfCan('view-budgetExpenses', Link::toRoute('budget.expense.index', __('Budget Expenses'))
                                ->addClass(route_is(['budget.expense.*']) ? 'active' : ''))
                            ->addIfCan('view-budgetRevenues', Link::toRoute('budget.revenue.index', __('Budget Revenue'))
                                ->addClass(route_is(['budget.revenue.*']) ? 'active' : ''))
                            ->addParentClass('submenu')
                    )
                    // Budget-related items (permission-gated)
                    ->addIfCan('view-budgetCategories', Link::toRoute('budget.categories.index', __('Categories'))
                        ->addClass(route_is(['budget.categories.*']) ? 'active' : ''))


                    ->addParentClass('submenu')
            );
        }
        // ==================== OPERATIONS ====================
        $operationsActive = route_is(['assets.*', 'folders.*', 'tickets.*', 'assigned-tickets']);
        $menu->submenu(
            Html::raw('<a href="#" class="' . $operationsActive . '"><i class="la la-briefcase"></i> <span>' . __('Operations') . '</span><span class="menu-arrow"></span></a>'),
            Menu::new()
                ->addParentClass('submenu')
                ->addIfCan('view-assets', Link::toRoute('assets.index', __('Assets'))->addClass(route_is('assets.*') ? 'active' : ''))
                ->add(Link::toRoute('folders.index', __('File Manager'))->addClass(route_is('folders.*') ? 'active' : ''))
                ->addIf(
                    function () {
                        return auth()->user()->type === UserType::EMPLOYEE;
                    },
                    Menu::new()
                        ->submenu(
                            Html::raw('<a href="#" class="' . (route_is(['tickets.*', 'assigned-tickets']) ? 'active' : '') . '"><span>' . __('Tickets') . '</span><span class="menu-arrow"></span></a>'),
                            Menu::new()
                                ->add(Link::toRoute('tickets.index', __('All Tickets'))->addClass(route_is('tickets.index') ? 'active' : ''))
                                ->add(Link::toRoute('assigned-tickets', __('My Assigned Tickets'))->addClass(route_is('assigned-tickets') ? 'active' : ''))
                        )
                )
                ->addIf(
                    function () {
                        return auth()->user()->type !== UserType::EMPLOYEE;
                    },
                    Link::toRoute('tickets.index', __('Tickets'))->addClass(route_is('tickets.*') ? 'active' : '')
                )
        );

        // Chat is now in the Apps section managed by Whiteboard module

        // ==================== SYSTEM ====================
        if (auth()->user()->canAny(['view-holidays', 'view-users', 'view-roles', 'view-backups', 'view-settings'])) {
            $menu->html('<span>Settings & Admin</span>', ['class' => 'menu-title']);
        }

        // Administration
        if (auth()->user()->canAny(['view-holidays', 'view-users', 'view-roles'])) {
            $activeClass = route_is(['holidays.*', 'users.*', 'roles.*']) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-shield"></i> <span>' . __('Administration') . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addParentClass('submenu')
                    ->addIfCan('view-holidays', Link::toRoute('holidays.index', __('Holidays'))->addClass(route_is('holidays.*') ? 'active' : ''))
                    ->addIfCan('view-users', Link::toRoute('users.index', __('User Management'))->addClass(route_is('users.*') ? 'active' : ''))
                    ->addIfCan('view-roles', Link::toRoute('roles.index', __('Roles & Permissions'))->addClass(route_is('roles.*') ? 'active' : ''))
            );
        }

        // System Settings
        if (auth()->user()->canAny(['view-backups', 'view-settings'])) {
            $activeClass = route_is(['settings.*', 'backups.*']) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-cog"></i> <span>' . __('System') . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addParentClass('submenu')
                    ->addIfCan('view-settings', Link::toRoute('settings.index', __('Settings'))->addClass(route_is('settings.*') ? 'active' : ''))
                    ->addIfCan('view-backups', Link::toRoute('backups.index', __('Backups'))->addClass(route_is('backups.*') ? 'active' : ''))
            );
        }
    }
}