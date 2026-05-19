<?php

use App\Http\Controllers\Admin\AdminListController;
use App\Http\Controllers\Admin\PurposeController;
use App\Http\Controllers\General\AdminController;
use App\Http\Controllers\General\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

$app_name = env('APP_NAME', '');

Route::redirect('/', "/$app_name");

Route::prefix($app_name)->middleware(AuthMiddleware::class)->group(function () {

  Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get("/admin", [AdminController::class, 'index'])->name('admin');
    Route::get("/new-admin", [AdminController::class, 'index_addAdmin'])->name('index_addAdmin');
    Route::post("/add-admin", [AdminController::class, 'addAdmin'])->name('addAdmin');
    Route::post("/remove-admin", [AdminController::class, 'removeAdmin'])->name('removeAdmin');
    Route::patch("/change-admin-role", [AdminController::class, 'changeAdminRole'])->name('changeAdminRole');

    // Purpose types
    Route::get('/admin/purposes', [PurposeController::class, 'index'])->name('admin.purposes.index');
    Route::post('/admin/purposes', [PurposeController::class, 'store'])->name('admin.purposes.store');
    Route::put('/admin/purposes/{id}', [PurposeController::class, 'update'])->name('admin.purposes.update');
    Route::delete('/admin/purposes/{id}', [PurposeController::class, 'destroy'])->name('admin.purposes.destroy');

    // Admin list
    Route::get('/admin/admin-list', [AdminListController::class, 'index'])->name('admin.admin-list.index');
    Route::get('/admin/admin-list/employees', [AdminListController::class, 'searchEmployees'])->name('admin.admin-list.employees');
    Route::post('/admin/admin-list', [AdminListController::class, 'store'])->name('admin.admin-list.store');
    Route::delete('/admin/admin-list/{id}', [AdminListController::class, 'destroy'])->name('admin.admin-list.destroy');
  });

  Route::get("/", [DashboardController::class, 'index'])->name('dashboard');
  Route::get("/profile", [ProfileController::class, 'index'])->name('profile.index');
  Route::post("/change-password", [ProfileController::class, 'changePassword'])->name('changePassword');
});
