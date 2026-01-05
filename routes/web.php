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


// Public landing routes
Route::view('/', 'landing.home')->name('landing.home');
Route::view('/apps', 'landing.apps')->name('landing.apps');
Route::view('/pricing', 'landing.pricing')->name('landing.pricing');
Route::view('/industries', 'landing.industries')->name('landing.industries');
Route::view('/services', 'landing.services')->name('landing.services');
Route::view('/resources', 'landing.resources')->name('landing.resources');

include __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('home', [DashboardController::class, 'index'])->name('home');
    // files route 


    Route::group(['middleware' => 'signed'], function () {
        Route::get('files/create', [FileController::class, 'create'])->name('files.create');
        Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
        Route::get('files/{file}/view', [FileController::class, 'view'])->name('files.view');
        Route::get('files/{folder}', [FileController::class, 'show'])->name('files.show');
        Route::delete('files/{folder}', [FileController::class, 'show'])->name('files.destroy');
    });


    //   Route::get('folders', [FolderController::class, 'index'])->name('folders');
    //   Route::get('folders/create',[FolderController::class,'create'])->name('folders.create');
    //   Route::post('folders/store',[FolderController::class,'store'])->name('folders.store');
    Route::resource('folders', FolderController::class);
    Route::get('/users/search', [FolderController::class, 'search'])->name('folder.users-search');
    Route::get('/users/preload', [FolderController::class, 'preload'])->name('folder.users-preload');


    Route::any('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('profile', [UserProfileController::class, 'index'])->name('profile');
    Route::get('profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [UserProfileController::class, 'update']);

    Route::group(['prefix' => 'apps'], function () {
        Route::get('chat/{contact?}', [ChatAppController::class, 'index'])->name('app.chat');
        Route::delete('delete-chat/{receiver}', [ChatAppController::class, 'destroy'])->name('chat.delete-conversation');
    });

    Route::resource('users', UsersController::class);
    Route::resource('employees', EmployeesController::class);
    Route::resource('clients', ClientsController::class);
    // Route::resource('contacts', ContactController::class);

    Route::get('client-list', [ClientsController::class, 'list'])->name('clients.list');
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
    Route::group(['prefix' => 'payroll'], function () {
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

    Route::get('employees-list', [EmployeesController::class, 'list'])->name('employees.list');
    Route::resource('departments', DepartmentsController::class)->except(['show']);
    Route::resource('designations', DesignationsController::class)->except(['show']);
    Route::resource('holidays', HolidaysController::class);
    Route::get('holidays-calendar', [HolidaysController::class, 'calendar'])->name('holidays.calendar');
    Route::resource('family-information', FamilyInfoController::class);
    Route::resource('assets', AssetsController::class);
    Route::get('backups', fn() => view('pages.backups', ['pageTitle' => __('Backups')]))->name('backups.index');
    Route::get('attendance', [AttendancesController::class, 'index'])->name('attendances.index');
    Route::get('attendance-details/{attendance}', [AttendancesController::class, 'attendanceDetails'])->name('attendance.details');
    Route::resource('tickets', TicketsController::class);
    Route::get('assigned-tickets', [TicketsController::class, 'assignedTickets'])->name('assigned-tickets');
    Route::post('assign-ticket', [TicketsController::class, 'assignUser'])->name('ticket.assign-user');

    Route::resource('leavetypes', LeaveTypeController::class);
    Route::resource('leaverequests', LeaveRequestController::class);
    Route::put('/leaverequests/{leaverequest}/{employee}', [LeaveRequestController::class, 'update'])
        ->name('leaverequests.update_status');
    Route::get('/myleaverequests', [LeaveRequestController::class, 'myLeaveRequests'])
        ->name('leaverequests.myleaverequests');
    // Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])
    //     ->name('leaverequests.show');



    Route::resource('annual_leaves', AnunalLeaveController::class);
    Route::get('app-logs', fn() => redirect()->to('log-viewer'))->name('app.logs');

    Route::get('evaluate', [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::get('assign-evaluator', [EvaluationController::class, 'assignEvaluatorView'])->name('evaluation.assign-evaluator');
    Route::post('evaluation/assign', [EvaluationController::class, 'assignEvaluator'])->name('evaluation.assign.post');
    Route::get('evaluate/{employee}', [EvaluationController::class, 'showEvaluationForm'])->name('evaluation.form');
    Route::post('evaluate/{employee}', [EvaluationController::class, 'submitEvaluation'])->name('evaluation.submit');
    Route::delete('evaluation/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluation.delete');


    //settings
    Route::prefix('settings')->group(function () {
        Route::get('company', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');

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
    Route::prefix('accounting-module')->name('accounting.')->group(function () {
        Route::resource('accounts', \App\Http\Controllers\AccountController::class);
        Route::get('accounts/{id}/balance', [\App\Http\Controllers\AccountController::class, 'getBalance'])->name('accounts.balance');
        
        Route::resource('account-types', \App\Http\Controllers\AccountTypeController::class);
        Route::resource('account-groups', \App\Http\Controllers\AccountGroupController::class);
    });

    // awards
    Route::resource('awards', AwardController::class);
});

Route::group(['prefix' => 'deposits-module', 'middleware' => ['auth']], function () {
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

Route::group(['middleware' => ['auth']], function () {
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
