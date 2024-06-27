<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Main\AccountController;
use App\Http\Controllers\Main\ReportController;
use App\Http\Controllers\Main\SettingController;
use App\Http\Middleware\mAuth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/frontend_register', [AccountController::class, 'register']);
Route::post('/signup', [AccountController::class, 'signup']);
Route::get('/frontend_login', [AccountController::class, 'login']);
Route::post('/login_action', [AccountController::class, 'login_action'])->name('login.action');
Route::get('/frontend_logout', [AccountController::class, 'logout']);
Route::get('/terimakasih', [AccountController::class, 'terimakasih']);
Route::get('/account_activate', [AccountController::class, 'account_activate']);


Route::group(['middleware' => 'mAuth'], function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::post('journal_table', [DashboardController::class, 'journal_table'])->name('journal.table');
    Route::get('/get_account_receive/{id}', [DashboardController::class, 'get_account_receive']);
    Route::post('/save_jurnal', [DashboardController::class, 'save_jurnal']);
    Route::get('journal_add', [DashboardController::class, 'journal_add']);
    Route::get('journal_edit/{id}', [DashboardController::class, 'journal_edit']);
    Route::get('journal_multiple_form', [DashboardController::class, 'journal_multiple_form']);
    Route::post('save_multiple_journal', [DashboardController::class, 'save_multiple_journal']);
    Route::post('confirm_journal_delete', [DashboardController::class, 'confirm_journal_delete']);
    Route::get('get_detail/{id}', [DashboardController::class, 'get_detail']);
    Route::post('journal_update', [DashboardController::class, 'journal_update']);
    Route::get('report', [ReportController::class, 'index']);
    Route::get('profit_loss', [ReportController::class, 'profit_loss']);
    Route::post('submit_profit_loss', [ReportController::class, 'submit_profit_loss']);
    Route::get('balance', [ReportController::class, 'balance']);
    Route::post('submit_balance_sheet', [ReportController::class, 'submit_balance_sheet']);
    Route::get('journal_report', [ReportController::class, 'journal_report']);
    Route::post('journal_report_submit', [ReportController::class, 'journal_report_submit']);
    Route::get('trial_balance', [ReportController::class, 'trial_balance']);
    Route::post('trial_balance_submit', [ReportController::class, 'trial_balance_submit']);
    Route::get('general_ledger', [ReportController::class, 'general_ledger']);
    Route::post('general_ledger_submit', [ReportController::class, 'general_ledger_submit']);

    Route::get('profit_loss_export/{tanggal}', [ReportController::class, 'profit_loss_export']);
    Route::get('balance_sheet_export/{tanggal}', [ReportController::class, 'balance_sheet_export']);



    Route::get('setting', [SettingController::class,'index']);
    Route::get('generate_opening_balance', [SettingController::class, 'generate_opening_balance']);
    Route::post('submit_opening_balance', [SettingController::class, 'submit_opening_balance']);

    Route::get('company_setting',[SettingController::class, 'company_setting']);
    Route::post('company_setting_update', [SettingController::class, 'company_setting_update']);
    Route::get('initial_capital', [SettingController::class, 'initial_capital']);
    Route::post('save_initial_capital', [SettingController::class, 'save_initial_capital']);
    Route::get('account_setting', [SettingController::class, 'account_setting']);
    Route::get('account_setting/{akun}', [SettingController::class, 'account_detail']);
    Route::post('save_setting_account', [SettingController::class, 'save_setting_account']);
});




// Route::get('/', function(){
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');



// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';
