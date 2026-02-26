<?php

use App\Http\Controllers\VariableController;
use App\Http\Controllers\WalletHistoryController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WebhookAdminController;
use App\Http\Controllers\WebhookViewController;

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

Route::get('/', [WebhookViewController::class, 'dashboard'])->name('webhook.index');


Route::get('/dashboard', [WebhookViewController::class, 'dashboard'])->name('dashboard');
Route::get('/manage', [WebhookAdminController::class, 'manage'])->name('admin.webhook.manage');

//With CSV File Upload and Individual Address Option
Route::post('/create', [WebhookAdminController::class, 'create'])->name('admin.webhook.create');
Route::post('/delete', [WebhookAdminController::class, 'delete'])->name('admin.webhook.delete');

// Add/Removed Addresses
Route::post('/patch', [WebhookAdminController::class, 'patch'])->name('admin.webhook.patch');

// Replace Addresses
Route::post('/replace', [WebhookAdminController::class, 'replace'])->name('admin.webhook.replace');

//Wallets History
Route::get('/wallets', [WalletHistoryController::class, 'index'])->name('wallets.index');
Route::get('/wallets/{wallet}', [WalletHistoryController::class, 'show'])->name('wallets.show');

// Webhook history
Route::get('/webhooks/history', [WebhookViewController::class, 'history'])->name('webhooks.history');
Route::get('/webhooks/{webhookId}/addresses', [WebhookViewController::class, 'showAddresses'])->name('webhooks.addresses');




// Custom Variable Controllers - Creations
Route::get('/variables/all', [VariableController::class, 'getAllVariables'])->name('variables.index');
Route::get('variables/view/{encryptedId}', [VariableController::class, 'viewVariableAddresses'])->name('variables.view');
Route::post('/variables/sync/{id}', [VariableController::class, 'syncVariable'])->name('variables.sync');

Route::get('/variables-manage', [VariableController::class, 'manage'])->name('variables.manage');

Route::prefix('variables')->name('variables.')->group(function () {
    Route::post('/create', [VariableController::class, 'createVariablesViaCsv'])->name('create'); 
    Route::post('/update', [VariableController::class, 'updateVariable'])->name('update');
    Route::post('/delete', [VariableController::class, 'deleteVariable'])->name('delete');
});

// Custom Variable Create WebHook Show Form
Route::get('/variables/webhook/create', [WebhookAdminController::class, 'showVariableWebhookForm'])->name('variables.webhook.create');

// Custom Variable After Custom-WebHook creations
Route::post('/create-variable-webhook', [WebhookAdminController::class, 'createVariableWebhook'])->name('admin.webhook.createVariable');

