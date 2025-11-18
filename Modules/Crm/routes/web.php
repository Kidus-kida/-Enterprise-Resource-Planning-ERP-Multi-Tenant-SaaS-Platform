<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Http\Controllers\CampaignController;
use Modules\Crm\Http\Controllers\LeadController;
use Modules\Crm\Http\Controllers\FollowUpController;
use Modules\Crm\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'crm','middleware' => ['auth']], function () {
    Route::resource('campaigns', CampaignController::class)->names('campaigns');
    Route::resource('leads', LeadController::class)->names('leads');
    Route::resource('follow-ups', FollowUpController::class)->names('follow-ups');
    Route::get('reports', [ReportController::class, 'index'])->name('crm-reports.index');
});
