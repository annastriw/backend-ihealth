<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiabetesScreeningController;
use App\Http\Controllers\UserScreeningController;
use App\Http\Controllers\Dashboard\DashboardController;

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

Route::get('/', function () {
    return view('welcome');
});

// Routes yang memerlukan authentication
Route::middleware(['auth'])->group(function () {
    
    // Dashboard utama
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Screening Routes (untuk user biasa)
    Route::prefix('dashboard/user-screening')->name('user.screening.')->group(function () {
        Route::get('/', [UserScreeningController::class, 'index'])->name('index');
        Route::get('/create', [UserScreeningController::class, 'create'])->name('create');
        Route::post('/store', [UserScreeningController::class, 'store'])->name('store');
        Route::get('/detail/{id}', [UserScreeningController::class, 'show'])->name('show');
    });
    
    // Diabetes Screening Routes (untuk tenaga kesehatan)
    Route::prefix('dashboard/medical/screening/diabetes-melitus')->group(function () {
        Route::get('/', [DiabetesScreeningController::class, 'index'])->name('diabetes.screening');
        Route::post('/predict', [DiabetesScreeningController::class, 'store'])->name('diabetes.predict');
        Route::get('/history', [DiabetesScreeningController::class, 'history'])->name('diabetes.history');
        Route::get('/dashboard', [DiabetesScreeningController::class, 'dashboard'])->name('diabetes.dashboard');
        Route::get('/chart-data', [DiabetesScreeningController::class, 'getChartData'])->name('diabetes.chart-data');
        Route::get('/{id}', [DiabetesScreeningController::class, 'show'])->name('diabetes.show');
        Route::delete('/{id}', [DiabetesScreeningController::class, 'destroy'])->name('diabetes.delete');
    });
    
    // Alternative shorter routes (optional)
    Route::prefix('diabetes')->group(function () {
        Route::get('/dashboard', [DiabetesScreeningController::class, 'dashboard'])->name('diabetes.dashboard.short');
        Route::get('/history', [DiabetesScreeningController::class, 'history'])->name('diabetes.history.short');
        Route::get('/screening', [DiabetesScreeningController::class, 'index'])->name('diabetes.screening.short');
    });
    
    
});