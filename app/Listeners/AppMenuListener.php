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
        $user = auth()->user();
        $user = auth()->user();
        $business = $user->business;

        // Dynamic Tenant Resolution: identify which business owns the current context
        // We prioritize the session 'current_tenant_id' set by SwitchTenantDatabase middleware
        $tenantBusinessId = null;
        $tenantId = session('current_tenant_id');
        
        if ($tenantId) {
            $tenantRecord = \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('tenants')
                ->where('id', $tenantId)
                ->first();
                
            if ($tenantRecord) {
                $tenantBusinessId = $tenantRecord->business_id;
            }
        } 
        
        // Fallback: Check if the current database connection name resembles a tenant DB
        // precise check if session didn't work (e.g. CLI or weird state)
        if (!$tenantBusinessId) {
             try {
                 // Check if tenant connection is configured before accessing
                 if (\Illuminate\Support\Facades\Config::has('database.connections.tenant')) {
                     $currentDb = \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName();
                     // Only query if we have a connection named 'tenant' that is different from 'mysql'
                     if ($currentDb && $currentDb !== \Illuminate\Support\Facades\DB::connection('mysql')->getDatabaseName()) {
                         $tenantRecord = \Illuminate\Support\Facades\DB::connection('mysql')
                            ->table('tenants')
                            ->where('database_name', $currentDb)
                            ->first();
                         if ($tenantRecord) {
                            $tenantBusinessId = $tenantRecord->business_id;
                         }
                     }
                 }
             } catch (\Exception $e) {
                 // Tenant connection might not be configured, ignore error
             }
        }

        // If the user's business_id doesn't match the tenant's business_id, prefer the tenant's ID
        // This fixes the case where seeding set user->business_id = 1, but the tenant is actually business_id = 6
        if ($tenantBusinessId && (!$business || $business->id != $tenantBusinessId)) {
             $business = \App\Business::on('mysql')->with('subscription')->find($tenantBusinessId);
        } elseif ($business && !$business->relationLoaded('subscription')) {
             // If business exists but subscription not loaded, load it explicitly
             $business->load('subscription');
        }
        
        // Ensure connection is correct if we found a business
        if ($business && $business->getConnectionName() !== 'mysql') {
            $business->setConnection('mysql');
            // Reload subscription after connection change
            $business->load('subscription');
        }

        // DEBUG: Write final state to log
        $logPath = public_path('debug_menu_log.txt');
        $debugData = [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user->id,
            'session_tenant_id' => $tenantId,
            'tenant_business_id_found' => $tenantBusinessId,
            'final_business_id' => $business ? $business->id : 'NULL',
            'subscription_loaded' => $business && $business->subscription ? 'YES' : 'NO',
            'sub_id' => $business && $business->subscription ? $business->subscription->id : 'N/A',
            'company_count' => $business && $business->subscription ? ($business->subscription->company_count ?? 'NULL') : 'N/A',
            'has_biz_settings_perm' => auth()->user()->can('business_settings.access') ? 'YES' : 'NO',
            'module_details' => $business && $business->subscription ? $business->subscription->module_activation_details : 'N/A'
        ];
        file_put_contents($logPath, print_r($debugData, true), FILE_APPEND);

        // Helper to check if module is enabled in subscription
        $isModuleEnabled = function($moduleKey) use ($user, $business) {
            // Super Admin always has access to everything
            if ($user->type === \App\Enums\UserType::SUPERADMIN) {
                return true;
            }
            
            if (!$business || !$business->subscription) return false;
            $perms = $business->subscription->module_activation_details ?? [];
            return isset($perms[$moduleKey]) && (bool)$perms[$moduleKey];
        };

        // ==================== DASHBOARD ====================
        $menu->add(
            Link::toRoute('dashboard', '<i class="la la-dashboard"></i> <span>' . __('Dashboard') . '</span>')
                ->setActive(route_is('dashboard'))
                ->setAttributes(['wire:navigate' => 'true'])
        );

        // ==================== SUPERADMIN ====================
        // Show only for system owners (not tenant owners)
        if (auth()->user()->isSystemOwner()) {
            $menu->add(
                Link::toRoute('superadmin.dashboard', '<i class="la la-user-shield"></i> <span>' . __('Superadmin') . '</span>')
                    ->setActive(route_is('superadmin.*'))
                    ->setAttributes(['wire:navigate' => 'true'])
            );
        }

        // ==================== HR MANAGEMENT ====================
        if (
            $isModuleEnabled('hr') &&
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
                        ->addIfCan('view-employees', Link::toRoute('employees.index', __('All Employees'))->addClass(route_is(['employees.index', 'employees.list']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('view-departments', Link::toRoute('departments.index', __('Departments'))->addClass(route_is('departments.index') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('view-designations', Link::toRoute('designations.index', __('Designations'))->addClass(route_is('designations.index') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('view-attendances', Link::toRoute('attendances.index', __('Attendance'))->addClass(route_is(['attendances.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                );
            }

            // Leave Management Submenu
            if (auth()->user()->canAny(['view-request', 'edit-request', 'create-annual-leave', 'create-leave-type'])) {
                $activeClass = route_is(['leaverequests.index', 'leaverequests.myleaverequests', 'leavetypes.index', 'annual_leaves.index']) ? "active" : "";
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-calendar-check-o"></i> <span>' . __('Leave Management') . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addParentClass('submenu')
                        ->addIfCan('edit-request', Link::toRoute('leaverequests.index', __('Leave Requests'))->addClass(route_is(['leaverequests.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('view-request', Link::toRoute('leaverequests.myleaverequests', __('My Leaves'))->addClass(route_is(['leaverequests.myleaverequests']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('create-leave-type', Link::toRoute('leavetypes.index', __('Leave Types'))->addClass(route_is(['leavetypes.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('create-annual-leave', Link::toRoute('annual_leaves.index', __('Annual Leave Settings'))->addClass(route_is(['annual_leaves.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                );
            }

            // Performance Submenu
            if (auth()->user()->canAny(['view-evaluation', 'view-evaluation-assignment', 'view-award'])) {
                $activeClass = route_is(['evaluation.index', 'evaluation.assign-evaluator', 'awards.*']) ? "active" : "";
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-chart-line"></i> <span>' . __('Performance') . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addParentClass('submenu')
                        ->addIfCan('view-evaluation', Link::toRoute('evaluation.index', __('Evaluations'))->addClass(route_is('evaluation.index') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('view-evaluation-assignment', Link::toRoute('evaluation.assign-evaluator', __('Evaluation Assignments'))->addClass(route_is('evaluation.assign-evaluator') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan('view-award', Link::toRoute('awards.index', __('Awards & Recognition'))->addClass(route_is('awards.*') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                );
            }

            // Payroll Submenu
            if ($isModuleEnabled('payroll') && auth()->user()->canAny(['view-PayrollAllowances', 'view-PayrollDeductions', 'view-payrolls', 'view-payslips'])) {
                $payrollActive = route_is(['payroll.*', 'payslips.*', 'allowances.*', 'deductions.*']);
                $menu->submenu(
                    Html::raw('<a href="#" class="' . $payrollActive . '"><i class="la la-money"></i><span>' . __("Payroll") . '</span><span class="menu-arrow"></span></a>'),
                    Menu::new()
                        ->addIf(
                            function () {
                                return auth()->user()->can(['view-PayrollAllowances', 'view-PayrollDeductions']);
                            },
                            Link::toRoute('payroll.items', __('Payroll Items'))->addClass(route_is(['payroll.items']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true'])
                        )
                        ->addIfCan("view-payrolls", Link::toRoute('payroll.processing.index', __('Payroll Processing'))->addClass(route_is(['payroll.processing.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                        ->addIfCan("view-payslips", Link::toRoute('payslips.index', __('Payslips'))->addClass(route_is(['payslips.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
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
                    ->addIfCan('view-projects', Link::toRoute('projects.index', __('Projects'))->addClass(route_is(['projects.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taskboards', Link::toRoute('task-boards.index', __('Default TaskBoards'))->addClass(route_is(['task-boards.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addParentClass('submenu')
            );
        }

        // CRM
        if ($isModuleEnabled('contacts') && auth()->user()->canAny(['view-clients', 'view-budgetCategories', 'view-budgets', 'view-budgetExpenses', 'view-budgetRevenues'])) {
            $activeClass = route_is(['clients.*', 'campaigns.*', 'leads.*', 'follow-ups.*', 'crm-reports.*']) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-handshake-o"></i> <span>' . __('CRM') . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addParentClass('submenu')
                    ->addIfCan('view-clients', Link::toRoute('clients.index', __('Clients'))->addClass(route_is('clients.*') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->add(Link::toRoute('leads.index', __('Leads'))->addClass(route_is(['leads.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->add(Link::toRoute('follow-ups.index', __('Follow-ups'))->addClass(route_is(['follow-ups.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-budgetCategories', Link::toRoute('campaigns.index', __('Campaigns'))->addClass(route_is(['campaigns.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->add(Link::toRoute('crm-reports.index', __('Report'))->addClass(route_is(['crm-reports.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
            );
        }

        // Purchase
        if($isModuleEnabled('purchases') && auth()->user()->canAny(['view-taxes','view-expenses','view-estimates','view-invoices'])){
            $activeClass = route_is(["taxes.*","expenses.*","estimates.*","invoices.*"]) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-shopping-bag"></i><span>' . __("Purchase") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addIfCan('view-taxes', Link::toRoute('purchase.index', __('List Purchases'))->addClass(route_is(['purchase.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-expenses', Link::toRoute('purchase.create', __('Add Purchase'))->addClass(route_is(['purchase.create']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-estimates', Link::toRoute('purchase.bulk_import', __('Bulk Purchase'))->addClass(route_is(['purchase.bulk_import']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-invoices', Link::toRoute('purchase-return.index', __('Purchase Returns'))->addClass(route_is(['purchase-return.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addParentClass('submenu')
            );
        }

        // Sales
        if (auth()->user()->canAny(['view-taxes', 'view-expenses', 'view-estimates', 'view-invoices'])) {
            $activeClass = route_is(["sales.*", "taxes.*", "expenses.*", "estimates.*", "invoices.*", "sales-return.*"]) ? "active" : "";
            $menu->submenu(
                Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-shopping-cart"></i><span>' . __("Sales") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addIfCan('view-taxes', Link::toRoute('sales.index', __('List Sales'))->addClass(request()->get('status') != 'order' && route_is(['sales.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-estimates', Link::toRoute('sales.create', __('Add Sales'))->addClass(route_is(['sales.create']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-estimates', Link::toRoute('sales.pos.create', __('POS'))->addClass(route_is(['sales.pos.create']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('sales.pos.index', __('List POS'))->addClass(route_is(['sales.pos.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('sales.subscriptions.index', __('Subscriptions'))->addClass(route_is(['sales.subscriptions.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('sales.draft.index', __('List Draft'))->addClass(route_is(['sales.draft.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('sales.quotation.index', __('List Quotation'))->addClass(route_is(['sales.quotation.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::to(route('sales.index', ['status' => 'order']), __('Sales Order'))->addClass(request()->get('status') == 'order' && route_is('sales.index') ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('sales.over_limit_sales', __('Over Limit Sales'))->addClass(route_is(['sales.over_limit_sales']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('sales-return.index', __('Sales Returns'))->addClass(route_is(['sales-return.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-taxes', Link::toRoute('taxes.index', __('Taxes'))->addClass(route_is(['taxes.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-expenses', Link::toRoute('expenses.index', __('Expenses'))->addClass(route_is(['expenses.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-estimates', Link::toRoute('estimates.index', __('Estimates'))->addClass(route_is(['estimates.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('view-invoices', Link::toRoute('invoices.index', __('Invoices'))->addClass(route_is(['invoices.*']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addParentClass('submenu')
            );
        }


        // Deposits
        if ($isModuleEnabled('deposits') && auth()->user()->can('deposits_module')) {
            $menu->add(
                Link::toRoute('deposits.index', '<i class="la la-money"></i> <span>' . __('Deposits') . '</span>')
                    ->addClass(route_is(['deposits.*']) ? 'active' : '')
                    ->setAttributes(['wire:navigate' => 'true'])
            );
        }

        // Stock Transfers
        if ($isModuleEnabled('products') && auth()->user()->canAny(['purchase.view', 'purchase.create'])) {
            $stockTransferActive = route_is(['stock-transfers.*', 'stock-transfers-request.*']);
            $menu->submenu(
                Html::raw('<a href="#" class="' . ($stockTransferActive ? 'active' : '') . '"><i class="la la-exchange"></i><span>' . __("Stock Transfers") . '</span><span class="menu-arrow"></span></a>'),
                Menu::new()
                    ->addIfCan('purchase.view', Link::toRoute('stock-transfers.index', __('All Stock Transfers'))->addClass(route_is(['stock-transfers.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('purchase.create', Link::toRoute('stock-transfers.create', __('Add Stock Transfer'))->addClass(route_is(['stock-transfers.create']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addIfCan('purchase.view', Link::toRoute('stock-transfers-request.index', __('Stock Transfer Requests'))->addClass(route_is(['stock-transfers-request.index']) ? 'active' : '')->setAttributes(['wire:navigate' => 'true']))
                    ->addParentClass('submenu')
            );
        }

        // Accounting
        if (
            $isModuleEnabled('accounting') &&
            auth()->user()->canAny([
                'view-budgetCategories',
                'view-budgets',
                'view-budgetExpenses',
                'view-budgetRevenues',
                // Add any other permission that should grant access to the full Accounting menu
                // e.g., 'view-accounts', 'view-journals', etc., if needed
            ])
        ) {
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
                    ->add(Link::toRoute('accounts.index', __('Accounts'))
                        ->addClass(route_is(['accounts.index', 'accounts.show']) ? 'active' : ''))
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
        if ($isModuleEnabled('operations')) {
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
        }

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
                    ->addIfCan('view-settings', Link::toRoute('settings.index', __('Settings'))->addClass(route_is('settings.index') ? 'active' : ''))
                    ->addIfCan('view-backups', Link::toRoute('backups.index', __('Backups'))->addClass(route_is('backups.*') ? 'active' : ''))
            );
        }
    }
}