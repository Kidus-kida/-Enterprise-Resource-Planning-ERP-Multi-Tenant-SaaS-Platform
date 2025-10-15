<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Http\Controllers\CampaignController;

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
});
