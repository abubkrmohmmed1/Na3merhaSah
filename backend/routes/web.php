<?php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Auth\LoginController; // Add this line
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication Routes (for admin web panel)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/kpi', [\App\Http\Controllers\Admin\AdminKPIController::class, 'index'])->name('admin.kpi');
    Route::get('/map', [AdminReportsController::class, 'map'])->name('admin.map');
    Route::get('/reports', [AdminReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/{id}', [AdminReportsController::class, 'show'])->name('admin.reports.show');
    Route::post('/reports/{id}', [AdminReportsController::class, 'update'])->name('admin.reports.update');
    Route::get('/reports/{id}/surveyor', [AdminReportsController::class, 'surveyor'])->name('admin.reports.surveyor');
    Route::post('/reports/{id}/surveyor', [AdminReportsController::class, 'updateSurveyor'])->name('admin.reports.surveyor.update');
    Route::get('/reports/{id}/approval', [AdminReportsController::class, 'approval'])->name('admin.reports.approval');
    Route::post('/reports/{id}/approval', [AdminReportsController::class, 'updateApproval'])->name('admin.reports.approval.update');
    Route::get('/reports/{id}/project', [AdminReportsController::class, 'project'])->name('admin.reports.project');
    Route::post('/reports/{id}/project', [AdminReportsController::class, 'updateProject'])->name('admin.reports.project.update');
    Route::get('/entities', [AdminUsersController::class, 'entities'])->name('admin.entities.index');
    Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users.index');
});
