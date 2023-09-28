<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\DiagnosisTypeController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');

Route::middleware(['auth:sanctum', 'role:superadmin|admin|teacher'])->group(function(){
    Route::get('/user/change-status/{user}', [UserController::class, 'changeStatus']);
    // User
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // Settings
    Route::get('/settings-value', [SettingsController::class, 'settingsKeyValue']);
    Route::resource('/settings', SettingsController::class);

    // Roles
    Route::get('/users/{user}/roles/{role}', [RoleController::class, 'assignRole']);
    Route::delete('/users/{user}/roles/{role}', [RoleController::class, 'removeRole']);
    Route::resource('/roles', RoleController::class);

    // permission
    Route::resource('/permissions', PermissionController::class);
    Route::get('/add-permission/{permission}/role/{role}', [PermissionController::class, 'givePermission']);
    Route::get('/revoke-permission/{permission}/role/{role}', [PermissionController::class, 'revokePermission']);
});

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/dashboard', [DashboardController::class, 'dashboard']);
});
