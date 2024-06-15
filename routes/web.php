<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Main\AccountController;

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

Route::get('/', [DashboardController::class, 'index']);
Route::get('/frontend_register', [AccountController::class, 'register']);
Route::post('/signup', [AccountController::class, 'signup']);
Route::get('/frontend_login', [AccountController::class, 'login']);
Route::post('/login_action', [AccountController::class, 'login_action'])->name('login.action');
Route::get('/frontend_logout', [AccountController::class, 'logout']);


// Route::get('/', function(){
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/get_account_receive/{id}', [DashboardController::class, 'get_account_receive']);
Route::post('/save_jurnal', [DashboardController::class, 'save_jurnal']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
