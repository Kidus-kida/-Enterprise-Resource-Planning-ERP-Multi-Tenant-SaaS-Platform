<?php

use App\Http\Controllers\AnunalLeaveController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\AllowancesController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\DeductionsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Admin\AssetsController;
use App\Http\Controllers\Admin\ChatAppController;
use App\Http\Controllers\Admin\ClientsController;
use App\Http\Controllers\Admin\TicketsController;
use App\Http\Controllers\Admin\HolidaysController;
use App\Http\Controllers\Admin\PayrollsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\FamilyInfoController;
use App\Http\Controllers\Admin\AttendancesController;
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\DesignationsController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Admin\EmployeeDetailsController;

use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;

use App\Http\Controllers\AwardController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\TaxCalculationController;
use App\Http\Controllers\DepositsController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockTransferRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


// Public landing routes
Route::view('/', 'landing.home')->name('landing.home');
Route::view('/apps', 'landing.apps')->name('landing.apps');
Route::view('/pricing', 'landing.pricing')->name('landing.pricing');
Route::view('/industries', 'landing.industries')->name('landing.industries');
Route::view('/services', 'landing.services')->name('landing.services');
Route::view('/resources', 'landing.resources')->name('landing.resources');
Route::view('/test-alpine', 'test-alpine');

// Database diagnostic routes (accessible without authentication)
Route::get('/tenant-login/{id}', [AuthController::class, 'tenantLogin']);

Route::get('/test-db-connection', function () {
    try {
        DB::connection()->getPdo();
        $tables = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [env('DB_DATABASE')]);
        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful',
            'database' => env('DB_DATABASE'),
            'tables' => $tables[0]->count ?? 0,
            'config' => [
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'username' => env('DB_USERNAME'),
                'password_set' => !empty(env('DB_PASSWORD'))
            ]
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'config' => [
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'username' => env('DB_USERNAME'),
                'password_set' => !empty(env('DB_PASSWORD'))
            ]
        ], 500);
    }
});
Route::get('/diagnostic', [\App\Http\Controllers\DiagnosticController::class, 'index'])->name('diagnostic.index');
Route::post('/diagnostic/fix-config', [\App\Http\Controllers\DiagnosticController::class, 'fixConfig'])->name('diagnostic.fix-config');
Route::post('/diagnostic/test-connection', [\App\Http\Controllers\DiagnosticController::class, 'testConnection'])->name('diagnostic.test-connection');
Route::post('/diagnostic/run-migrations', [\App\Http\Controllers\DiagnosticController::class, 'runMigrations'])->name('diagnostic.run-migrations');

// Simple test route to bypass middleware
Route::get('/test-route', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Route is working!',
        'timestamp' => now(),
        'database_status' => 'checking...'
    ]);
})->name('test.route');

// Test dashboard without authentication
Route::get('/test-dashboard', function() {
    try {
        // Test basic database queries
        $userCount = \App\Models\User::count();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard test successful!',
            'user_count' => $userCount,
            'database_connection' => 'working',
            'timestamp' => now(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Dashboard test failed: ' . $e->getMessage(),
            'timestamp' => now(),
        ], 500);
    }
})->name('test.dashboard');

// Test login page (should redirect to login if not authenticated)
Route::get('/test-auth', function() {
    try {
        if (auth()->check()) {
            return response()->json([
                'status' => 'authenticated',
                'user' => auth()->user()->email ?? 'no email',
                'message' => 'User is logged in',
            ]);
        } else {
            return response()->json([
                'status' => 'not_authenticated',
                'message' => 'User is not logged in',
                'login_url' => route('login'),
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Auth test failed: ' . $e->getMessage(),
        ], 500);
    }
})->name('test.auth');

// Test database tables
Route::get('/test-tables', function() {
    try {
        $results = [];
        
        // Test basic database connection
        $results['database_connection'] = 'OK';
        
        // Test if users table exists
        try {
            $userCount = DB::table('users')->count();
            $results['users_table'] = "OK (count: $userCount)";
        } catch (\Exception $e) {
            $results['users_table'] = "ERROR: " . $e->getMessage();
        }
        
        // Test if migrations table exists
        try {
            $migrationCount = DB::table('migrations')->count();
            $results['migrations_table'] = "OK (count: $migrationCount)";
        } catch (\Exception $e) {
            $results['migrations_table'] = "ERROR: " . $e->getMessage();
        }
        
        // Test User model
        try {
            $userModelCount = \App\Models\User::count();
            $results['user_model'] = "OK (count: $userModelCount)";
        } catch (\Exception $e) {
            $results['user_model'] = "ERROR: " . $e->getMessage();
        }
        
        return response()->json([
            'status' => 'success',
            'results' => $results,
            'timestamp' => now(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Table test failed: ' . $e->getMessage(),
            'timestamp' => now(),
        ], 500);
    }
})->name('test.tables');

// Test login page access
Route::get('/test-login-page', function() {
    try {
        // Just try to load the login view without going through middleware
        return view('auth.login', ['pageTitle' => 'Test Login']);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Login page test failed: ' . $e->getMessage(),
            'timestamp' => now(),
        ], 500);
    }
})->name('test.login.page');

// Test middleware stack step by step
Route::get('/test-middleware', function() {
    try {
        $results = [];
        
        // Test 1: Basic request info
        $results['request'] = [
            'url' => request()->url(),
            'method' => request()->method(),
            'ip' => request()->ip(),
        ];
        
        // Test 2: Session
        $results['session'] = [
            'started' => session()->isStarted(),
            'id' => session()->getId(),
            'tenant_id' => session('current_tenant_id', 'not set'),
        ];
        
        // Test 3: Auth system
        $results['auth'] = [
            'guard' => config('auth.defaults.guard'),
            'provider' => config('auth.defaults.provider'),
            'user_model' => config('auth.providers.users.model'),
            'check_works' => 'testing...',
        ];
        
        try {
            $results['auth']['check_works'] = auth()->check() ? 'yes' : 'no';
            $results['auth']['user_id'] = auth()->id() ?? 'not logged in';
        } catch (\Exception $e) {
            $results['auth']['check_works'] = 'error: ' . $e->getMessage();
        }
        
        // Test 4: Database connections
        $results['database'] = [
            'default_connection' => config('database.default'),
            'tenant_connection_configured' => !empty(config('database.connections.tenant')),
        ];
        
        // Test 5: Modules
        $results['modules'] = [
            'function_exists' => function_exists('module'),
        ];
        
        if (function_exists('module')) {
            try {
                $results['modules']['project_enabled'] = module('Project') ? module('Project')->isEnabled() : 'not found';
                $results['modules']['sales_enabled'] = module('Sales') ? module('Sales')->isEnabled() : 'not found';
            } catch (\Exception $e) {
                $results['modules']['error'] = $e->getMessage();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'results' => $results,
            'timestamp' => now(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Middleware test failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => now(),
        ], 500);
    }
})->name('test.middleware');

// Test dashboard controller directly (bypass auth middleware)
Route::get('/test-dashboard-direct', function() {
    try {
        // Create a fake authenticated user for testing
        $user = \App\Models\User::first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found in database',
            ], 500);
        }
        
        // Temporarily log in the user
        auth()->login($user);
        
        // Try to instantiate the dashboard controller
        $controller = new \App\Http\Controllers\DashboardController();
        
        // Try to call the index method
        $response = $controller->index();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard controller works!',
            'response_type' => get_class($response),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Dashboard controller test failed: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
            'timestamp' => now(),
        ], 500);
    }
})->name('test.dashboard.direct');

// Test step-by-step dashboard components
Route::get('/test-dashboard-parts', function() {
    try {
        $results = [];
        
        // Step 1: Test user authentication
        $user = \App\Models\User::first();
        if (!$user) {
            return response()->json(['error' => 'No users found'], 500);
        }
        auth()->login($user);
        $results['auth'] = 'OK - User logged in: ' . $user->email;
        
        // Step 2: Test UserType enum
        $results['user_type'] = 'User type: ' . $user->type->value;
        
        // Step 3: Test module function
        $results['module_function'] = function_exists('module') ? 'OK' : 'Missing';
        
        // Step 4: Test specific modules
        if (function_exists('module')) {
            $results['project_module'] = module('Project') ? 'Found' : 'Not found';
            $results['sales_module'] = module('Sales') ? 'Found' : 'Not found';
            $results['accounting_module'] = module('Accounting') ? 'Found' : 'Not found';
        }
        
        // Step 5: Test basic database queries
        $results['user_count'] = \App\Models\User::count();
        
        // Step 6: Test if Ticket model exists
        try {
            $results['ticket_model'] = class_exists('App\Models\Ticket') ? 'Exists' : 'Missing';
            if (class_exists('App\Models\Ticket')) {
                $results['ticket_count'] = \App\Models\Ticket::count();
            }
        } catch (\Exception $e) {
            $results['ticket_error'] = $e->getMessage();
        }
        
        // Step 7: Test view existence
        $results['dashboard_view'] = view()->exists('pages.dashboard') ? 'Exists' : 'Missing';
        $results['employee_dashboard_view'] = view()->exists('pages.employees.dashboard') ? 'Exists' : 'Missing';
        
        return response()->json([
            'status' => 'success',
            'results' => $results,
            'timestamp' => now(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'timestamp' => now(),
        ], 500);
    }
})->name('test.dashboard.parts');

include __DIR__ . '/auth.php';

Route::middleware([\App\Http\Middleware\SwitchTenantDatabase::class, 'auth'])->group(function () {
    // Add a test route within the authenticated group to isolate the issue
    Route::get('test-auth-middleware', function() {
        return response()->json([
            'status' => 'success',
            'message' => 'Auth middleware works!',
            'user' => auth()->user() ? auth()->user()->email : 'not logged in',
            'timestamp' => now(),
        ]);
    })->name('test.auth.middleware');
    
    // Simplified dashboard route for testing
    Route::get('dashboard-simple', function() {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            $data = [
                'pageTitle' => 'Dashboard',
                'user' => $user,
                'userCount' => \App\Models\User::count(),
            ];
            
            // Try to return a simple view first
            return view('pages.dashboard-simple', $data);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Dashboard error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    })->name('dashboard.simple');
    
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('home', [DashboardController::class, 'index'])->name('home');
    
    // Utilities
    Route::get('backups', fn() => view('pages.backups', ['pageTitle' => __('Backups')]))->name('backups.index');
    Route::get('app-logs', fn() => redirect()->to('log-viewer'))->name('app.logs');

    // files route 


    Route::group(['middleware' => 'signed'], function () {
        Route::get('files/create', [FileController::class, 'create'])->name('files.create');
        Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
        Route::get('files/{file}/view', [FileController::class, 'view'])->name('files.view');
        Route::get('files/{folder}', [FileController::class, 'show'])->name('files.show');
        Route::delete('files/{folder}', [FileController::class, 'show'])->name('files.destroy');
    });



    Route::any('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('profile', [UserProfileController::class, 'index'])->name('profile');
    Route::get('profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [UserProfileController::class, 'update']);

    Route::group(['prefix' => 'apps'], function () {
        Route::get('chat/{contact?}', [ChatAppController::class, 'index'])->name('app.chat');
        Route::delete('delete-chat/{receiver}', [ChatAppController::class, 'destroy'])->name('chat.delete-conversation');
    });

    //Route::resource('roles', \App\Http\Controllers\TenantRoleController::class);
    Route::resource('users', UsersController::class);
    // Contacts/Clients Module Routes
    Route::group(['middleware' => ['module.access:contacts']], function () {
        Route::resource('clients', ClientsController::class);
        Route::get('client-list', [ClientsController::class, 'list'])->name('clients.list');
    });

    // HR Module Routes
    Route::group(['middleware' => ['module.access:hr']], function () {
        Route::resource('employees', EmployeesController::class);
        Route::get('employees-list', [EmployeesController::class, 'list'])->name('employees.list');
        
        Route::resource('departments', DepartmentsController::class)->except(['show']);
        Route::resource('designations', DesignationsController::class)->except(['show']);
        Route::resource('holidays', HolidaysController::class);
        Route::get('holidays-calendar', [HolidaysController::class, 'calendar'])->name('holidays.calendar');
        Route::resource('family-information', FamilyInfoController::class);

        
        
        Route::get('attendance', [AttendancesController::class, 'index'])->name('attendances.index');
        Route::get('attendance-details/{attendance}', [AttendancesController::class, 'attendanceDetails'])->name('attendance.details');
        

        Route::resource('leavetypes', LeaveTypeController::class);
        Route::resource('leaverequests', LeaveRequestController::class);
        Route::put('/leaverequests/{leaverequest}/{employee}', [LeaveRequestController::class, 'update'])
            ->name('leaverequests.update_status');
        Route::get('/myleaverequests', [LeaveRequestController::class, 'myLeaveRequests'])
            ->name('leaverequests.myleaverequests');

        Route::resource('annual_leaves', AnunalLeaveController::class);
        Route::resource('awards', AwardController::class);

        // Evaluation
        Route::get('evaluate', [EvaluationController::class, 'index'])->name('evaluation.index');
        Route::get('assign-evaluator', [EvaluationController::class, 'assignEvaluatorView'])->name('evaluation.assign-evaluator');
        Route::post('evaluation/assign', [EvaluationController::class, 'assignEvaluator'])->name('evaluation.assign.post');
        Route::get('evaluate/{employee}', [EvaluationController::class, 'showEvaluationForm'])->name('evaluation.form');
        Route::post('evaluate/{employee}', [EvaluationController::class, 'submitEvaluation'])->name('evaluation.submit');
        Route::delete('evaluation/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluation.delete');

        // Employee Details
        Route::get('employee/personal-info/{employeeDetail}', [EmployeeDetailsController::class, 'personalInfo'])->name('employee.personal-info');
        Route::post('employee/personal-info/{employeeDetail}', [EmployeeDetailsController::class, 'updatePersonalInfo']);
        Route::get('employee/emergency-contacts/{employeeDetail}', [EmployeeDetailsController::class, 'emergencyContacts'])->name('employee.emergency-contacts');
        Route::post('employee/emergency-contacts/{employeeDetail}', [EmployeeDetailsController::class, 'updateEmergencyContacts']);
        Route::get('employee/experience/{employeeDetail}', [EmployeeDetailsController::class, 'workExperience'])->name('employee.experience');
        Route::post('employee/experience/{employeeDetail}', [EmployeeDetailsController::class, 'updateWorkExperience']);
        Route::delete('delete-experience/{experience}', [EmployeeDetailsController::class, 'deleteWorkExperience'])->name('employee.experience.delete');
        Route::get('employee/education/{employeeDetail}', [EmployeeDetailsController::class, 'education'])->name('employee.education');
        Route::post('employee/education/{employeeDetail}', [EmployeeDetailsController::class, 'updateEducation'])->name('employee-education.update');
        Route::delete('del-employee-education', [EmployeeDetailsController::class, 'deleteEducation'])->name('employee.education.delete');
        Route::post('employee-salary-setting/{employeeDetail}', [EmployeeDetailsController::class, 'salarySetting'])->name('employee.salary-setting');
    });


    // Payroll Module Routes
    Route::group(['prefix' => 'payroll', 'middleware' => ['module.access:payroll']], function () {
        Route::get('items', [PayrollsController::class, 'items'])->name('payroll.items');

        // Payroll Processing
        Route::get('processing', [\App\Http\Controllers\PayrollProcessingController::class, 'index'])->name('payroll.processing.index');
        Route::get('processing/create', [\App\Http\Controllers\PayrollProcessingController::class, 'create'])->name('payroll.processing.create');
        Route::post('processing/select-employees', [\App\Http\Controllers\PayrollProcessingController::class, 'selectEmployees'])->name('payroll.processing.select-employees');
        Route::post('processing/store', [\App\Http\Controllers\PayrollProcessingController::class, 'store'])->name('payroll.processing.store');
        Route::get('processing/{batch}', [\App\Http\Controllers\PayrollProcessingController::class, 'show'])->name('payroll.processing.show');
        Route::get('processing/{batch}/export', [\App\Http\Controllers\PayrollProcessingController::class, 'export'])->name('payroll.processing.export');
        Route::post('processing/{batch}/approve', [\App\Http\Controllers\PayrollProcessingController::class, 'approve'])->name('payroll.processing.approve');

        Route::resource('allowances', AllowancesController::class)->except(['show']);
        Route::post('allowances/update-or-create', [AllowancesController::class, 'updateOrCreate'])->name('allowances.updateOrCreate');
        Route::delete('allowances/delete-by-employee', [AllowancesController::class, 'deleteByEmployeeAndName'])->name('allowances.deleteByEmployee');
        Route::resource('deductions', DeductionsController::class)->except(['show']);
        Route::post('deductions/update-or-create', [DeductionsController::class, 'updateOrCreate'])->name('deductions.updateOrCreate');
        Route::delete('deductions/delete-by-employee', [DeductionsController::class, 'deleteByEmployeeAndName'])->name('deductions.deleteByEmployee');
        Route::resource('payslips', PayrollsController::class);
    });

    //settings
    Route::prefix('settings')->group(function () {
        Route::get('company', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');

        Route::resource('multi-companies', \App\Http\Controllers\CompanyController::class);
        Route::get('multi-companies/switch/{id}', [\App\Http\Controllers\CompanyController::class, 'switch'])->name('multi-companies.switch');
        Route::post('multi-companies/switch-multiple', [\App\Http\Controllers\CompanyController::class, 'switchMultiple'])->name('multi-companies.switch-multiple');

        Route::get('business', [\App\Http\Controllers\BusinessSettingsController::class, 'index'])->name('settings.business.index');
        Route::post('business', [\App\Http\Controllers\BusinessSettingsController::class, 'update'])->name('settings.business.update');

        Route::get('locale', [SettingsController::class, 'locale'])->name('settings.locale');
        Route::post('locale', [SettingsController::class, 'updateLocale'])->name('settings.locale.update');
        Route::get('theme', [SettingsController::class, 'theme'])->name('settings.theme');
        Route::post('theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
        Route::get('invoice', [SettingsController::class, 'invoice'])->name('settings.invoice');
        Route::post('invoice', [SettingsController::class, 'updateInvoice'])->name('settings.invoice.update');
        Route::get('salary', [SettingsController::class, 'salary'])->name('settings.salary');
        Route::post('salary', [SettingsController::class, 'updateSalarySettings'])->name('settings.salary.update');
        Route::get('mail', [SettingsController::class, 'email'])->name('settings.mail');
        Route::post('mail', [SettingsController::class, 'updateEmail'])->name('settings.mail.update');
        Route::get('payroll', [SettingsController::class, 'payroll'])->name('settings.payroll');
        Route::post('payroll', [SettingsController::class, 'updatePayrollSettings'])->name('settings.payroll.update');
        Route::post('payroll/tax-brackets', [SettingsController::class, 'updateTaxBrackets'])->name('settings.payroll.tax-brackets.update');

        // Business Locations
        Route::get('location/{location}/activate-deactivate', [\App\Http\Controllers\BusinessLocationController::class, 'activateDeactivateLocation'])->name('settings.location.activate-deactivate');
        Route::resource('location', \App\Http\Controllers\BusinessLocationController::class)->names('settings.location');
        
        // Invoice Settings
        Route::get('invoice-schemes/{id}/set-default', [\App\Http\Controllers\InvoiceSchemeController::class, 'setDefault'])->name('settings.invoice-schemes.set-default');
        Route::resource('invoice-schemes', \App\Http\Controllers\InvoiceSchemeController::class)->names('settings.invoice-schemes');
        Route::resource('invoice-layouts', \App\Http\Controllers\InvoiceLayoutController::class)->names('settings.invoice-layouts');
    });

    // tax calculation
    Route::prefix('payroll')->group(function () {

        Route::resource('tax', TaxCalculationController::class)
            ->names('payroll.tax');

    });


    // Accounting Module
    Route::prefix('accounting-module')->name('accounting.')->middleware(['module.access:accounting'])->group(function () {
        Route::resource('accounts', \App\Http\Controllers\AccountController::class);
        Route::get('accounts/{id}/balance', [\App\Http\Controllers\AccountController::class, 'getBalance'])->name('accounts.balance');
        
        Route::resource('account-types', \App\Http\Controllers\AccountTypeController::class);
        Route::resource('account-groups', \App\Http\Controllers\AccountGroupController::class);
    });

    // Operations Module
    Route::group(['middleware' => ['module.access:operations']], function () {
        Route::resource('assets', AssetsController::class);
        // File Manager / Folders
        Route::resource('folders', FolderController::class);
        Route::get('/users/search', [FolderController::class, 'search'])->name('folder.users-search');
        Route::get('/users/preload', [FolderController::class, 'preload'])->name('folder.users-preload');
        
        // Tickets
        Route::resource('tickets', TicketsController::class);
        Route::get('assigned-tickets', [TicketsController::class, 'assignedTickets'])->name('assigned-tickets');
        Route::post('assign-ticket', [TicketsController::class, 'assignUser'])->name('ticket.assign-user');
    });

    // awards
    Route::resource('awards', AwardController::class);
});

Route::group(['prefix' => 'deposits-module', 'middleware' => ['auth', 'module.access:accounting']], function () {
    Route::get('/check-insufficient-balance-for-accounts', [DepositsController::class, 'getAccsForWhichToCheckInsufficientBalances']);// @eng 15/2

    Route::get('/get-account-dp', [DepositsController::class, 'getBankAccountDropDown']);

    Route::get('/get-account-group-name-dp', [DepositsController::class, 'getBankAccountByGroupDP']);

    Route::get('/get-account-by-group-id/{group_id}', [DepositsController::class, 'getAccountByGroupId']);

    Route::get('/get-account-group-by-account/{type_id}', [DepositsController::class, 'getAccountGroupByAccount']);

    Route::get('/get-parent-account-by-type/{type_id}', [DepositsController::class, 'getParentAccountsByType']);

    Route::get('/account/image-modal', [DepositsController::class, 'imageModal']);

    Route::resource('/deposits', DepositsController::class);
    Route::get('/check_account_names', [DepositsController::class, 'checkAccountNames']);
    Route::get('/check_account_number', [DepositsController::class, 'checkAccountNumber']);
    Route::post('/account_details', [DepositsController::class, 'account_details']);

    Route::get('/fund-transfer', [DepositsController::class, 'getFundTransfer'])->name('deposits.getFundTransfer');

    Route::get('/account-number/{id}', [DepositsController::class, 'getAccNo']);

    Route::post('/fund-transfer', [DepositsController::class, 'postFundTransfer'])->name('deposits.postFundTransfer');

    Route::get('/cheque-list', [DepositsController::class, 'getChequeList'])->name('deposits.getChequeList');

    Route::post('/cheque-deposit', [DepositsController::class, 'postChequeDeposit'])->name('deposits.postChequeDeposit');

    Route::get('/cheque-deposit', [DepositsController::class, 'getChequeDeposit'])->name('deposits.getChequeDeposit');

    Route::get('/realize-cheque-deposit', [DepositsController::class, 'getRealizeChequeDeposit'])->name('deposits.getRealizeChequeDeposit');
    Route::get('/realize-cheque-list', [DepositsController::class, 'getRealizeChequeList'])->name('deposits.getRealizeChequeList');
    Route::post('/realize-cheque-deposit', [DepositsController::class, 'postRealizeChequeDeposit'])->name('deposits.postRealizeChequeDeposit');

    Route::get('/deposit/{id}', [DepositsController::class, 'getDeposit'])->name('deposits.getDeposit');

    Route::post('/deposit', [DepositsController::class, 'postDeposit'])->name('deposits.postDeposit');

    Route::get('/get-account-balance/{id}', [DepositsController::class, 'getAccountBalance']);

    Route::get('/list-deposit-transfer', [DepositsController::class, 'listDepositTransfer']);

    Route::get('/cheques-ob-details', [DepositsController::class, 'chequeObTransfer']);

    Route::get('/edit-deposit-transfer/{id}', [DepositsController::class, 'editDepositTransfer']);

    Route::post('/update-deposit-transfer/{id}', [DepositsController::class, 'updateDepositTransfer']);
    Route::get('/cash-flow', [DepositsController::class, 'cashFlow']);
    Route::get('/notes/{id}', [DepositsController::class, 'getNotes']);
    Route::get('/disabled-account', [DepositsController::class, 'disabledAccount']);
    Route::get('/reconcile/{id}', [DepositsController::class, 'reconcile']);
    Route::post('/disabled-status/{id}', [DepositsController::class, 'disabledStatus']);
    Route::post('/close/{id}', [DepositsController::class, 'close']);
    Route::delete('/account-transaction/{id}', [DepositsController::class, 'destroyAccountTransaction']);
});

Route::group(['middleware' => ['auth', 'module.access:products']], function () {
    Route::resource('stock-transfers', StockTransferController::class);
    Route::get('stock-transfers/print/{id}', [StockTransferController::class, 'printInvoice'])->name('stock-transfers.print');
    Route::get('/stock-transfer/get_transfer_location/{id}', [StockTransferController::class, 'getBusinessLocationExcept']);
    Route::get('/stock-transfer/get_transfer_store_id/{id}', [StockTransferController::class, 'getBusinessLocationStoreId']);
    Route::get('/store-list', [StockTransferController::class, 'getBusinessLocationStoreId']);

    Route::resource('stock-transfers-request', StockTransferRequestController::class);
    Route::post('stock-transfers-request/save-transfer', [StockTransferRequestController::class, 'saveTransfer'])->name('stock-transfer.savetransfer');
    Route::get('stock-transfers-request/create-transfer/{id}', [StockTransferRequestController::class, 'createTransfer'])->name('stock-transfers-request.createTransfer');
    Route::get('stock-transfers-request/get-product-balance', [StockTransferRequestController::class, 'getProductBalance']);

    Route::get('stock-transfers-request/received-transfer/{id}', [StockTransferRequestController::class, 'getReceivedTrasnfer'])->name('stock-transfers-request.getReceivedTrasnfer');
    Route::post('stock-transfers-request/received-transfer/{id}', [StockTransferRequestController::class, 'postReceivedTransfer'])->name('stock-transfers-request.postReceivedTransfer');
    Route::get('shippment', [StockTransferRequestController::class, 'shippment'])->name('stock-transfers-request.shippment');
    Route::post('addshipment', [StockTransferRequestController::class, 'addshipment'])->name('stock-transfers-request.addshipment');
    Route::get('shippment-detail/{id}', [StockTransferRequestController::class, 'showShipmentDetail'])->name('stock-transfers-request.showShipmentDetail');
    Route::get('edit-shipping/{id}', [StockTransferRequestController::class, 'editShipping'])->name('stock-transfers-request.editShipping');
    Route::match(['put', 'post'], 'update-shipping/{id}', [StockTransferRequestController::class, 'updateShipping'])->name('stock-transfers-request.updateShipping');
    Route::delete('destroy-shipping/{id}', [StockTransferRequestController::class, 'destroyShipping'])->name('stock-transfers-request.destroyShipping');
    Route::get('shippment-list', [StockTransferRequestController::class, 'shippment_list'])->name('stock-transfers-request.shippment_list');

});


// Emergency DB Fix Route - FULL SCAN
Route::get('/emergency-db-fix', function() {
    $results = [];
    $dbName = \DB::connection()->getDatabaseName();
    $results[] = "Connected to Database: " . $dbName;
    
    // Get ALL tables dynamically
    $tables = [];
    try {
        $tables = \Illuminate\Support\Facades\Schema::getTableListing();
    } catch (\Exception $e) {
        // Fallback for older Laravel
        $tables = \DB::connection()->getDoctrineSchemaManager()->listTableNames();
    }

    $results[] = "Scanning " . count($tables) . " tables...";
    
    foreach ($tables as $table) {
        if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            
            // Skip tables that shouldn't have company_id
            if (in_array($table, ['migrations', 'password_resets', 'failed_jobs', 'jobs', 'sessions', 'cache', 'activity_log', 'notifications'])) {
                continue;
            }

            if (!in_array('company_id', $columns)) {
                $results[] = "MISSING company_id in: $table";
                
                // Check if 'id' column exists to determine placement
                $placement = in_array('id', $columns) ? 'AFTER id' : '';
                
                try {
                    \DB::statement("ALTER TABLE $table ADD COLUMN company_id BIGINT UNSIGNED NULL $placement, ADD INDEX(company_id)");
                    $results[] = "SUCCESS: Added company_id to $table";
                } catch (\Exception $e) {
                    $results[] = "ERROR adding to $table: " . $e->getMessage();
                }
            }
        }
    }
    
    if (empty($results)) {
        $results[] = "All tables look good!";
    }
    
    return response()->json($results);
});
// End of file
