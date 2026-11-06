<?php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMapController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.kpi');
});

// Authentication Routes (for admin web panel)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Company Portal (B2B clients)
Route::prefix('company')->name('company.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Company\PortalController::class, 'login'])->name('login');
    Route::post('/authenticate', [\App\Http\Controllers\Company\PortalController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [\App\Http\Controllers\Company\PortalController::class, 'logout'])->name('logout');

    Route::middleware('company.auth')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Company\PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/code-examples', [\App\Http\Controllers\Company\PortalController::class, 'codeExamples'])->name('code-examples');
        Route::get('/segments', [\App\Http\Controllers\Company\PortalController::class, 'segments'])->name('segments');
        Route::get('/segments/{segment}', [\App\Http\Controllers\Company\PortalController::class, 'segmentDetail'])->name('segment-detail');
        Route::get('/widget/autocomplete', [\App\Http\Controllers\Company\WidgetController::class, 'autocomplete'])->name('widget.autocomplete');
        Route::get('/my-locations', [\App\Http\Controllers\Company\PortalController::class, 'myLocations'])->name('my-locations');
        Route::post('/add-location', [\App\Http\Controllers\Company\PortalController::class, 'addLocation'])->name('add-location');
    });
});

// Widget JS (public, for embedding)
Route::get('/company/widget/autocomplete.js', [\App\Http\Controllers\Company\WidgetController::class, 'autocompleteJs']);

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/kpi', [\App\Http\Controllers\Admin\AdminKPIController::class, 'index'])->name('admin.kpi');
    Route::get('/map', [AdminMapController::class, 'index'])->name('admin.map');
    Route::get('/map/data', [AdminMapController::class, 'mapData'])->name('admin.map.data');
    Route::get('/entities', [AdminUsersController::class, 'entities'])->name('admin.entities.index');
    Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users.index');

    // Spatial Management
    Route::get('/spatial/features', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'index'])->name('admin.spatial.index');
    Route::get('/spatial/features/{id}/edit', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'edit'])->name('admin.spatial.edit');
    Route::post('/spatial/features/{id}', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'update'])->name('admin.spatial.update');
    Route::post('/spatial/features/{id}/verify', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'verify'])->name('admin.spatial.verify');
    Route::delete('/spatial/features/{id}', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'destroy'])->name('admin.spatial.destroy');
    Route::get('/spatial/import', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'importForm'])->name('admin.spatial.import');
    Route::post('/spatial/import', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'importCsv'])->name('admin.spatial.import.csv');
    Route::post('/spatial/import-city', [\App\Http\Controllers\Admin\GeoFeatureController::class, 'importCity'])->name('admin.spatial.import.city');

    // Submissions Review
    Route::get('/submissions', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'index'])->name('admin.submissions.index');
    Route::post('/submissions/address/{id}/approve', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'approveAddress'])->name('admin.submissions.address.approve');
    Route::post('/submissions/address/{id}/reject', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'rejectAddress'])->name('admin.submissions.address.reject');
    Route::post('/submissions/proposal/{id}/approve', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'approveProposal'])->name('admin.submissions.proposal.approve');
    Route::post('/submissions/proposal/{id}/reject', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'rejectProposal'])->name('admin.submissions.proposal.reject');
    Route::post('/submissions/mobile/{id}/approve', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'approveMobile'])->name('admin.submissions.mobile.approve');
    Route::post('/submissions/mobile/{id}/reject', [\App\Http\Controllers\Admin\SubmissionsReviewController::class, 'rejectMobile'])->name('admin.submissions.mobile.reject');

    // Companies (B2B)
    Route::prefix('companies')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CompaniesController::class, 'index'])->name('admin.companies.index');
        Route::get('/create', [\App\Http\Controllers\Admin\CompaniesController::class, 'create'])->name('admin.companies.create');
        Route::post('/', [\App\Http\Controllers\Admin\CompaniesController::class, 'store'])->name('admin.companies.store');
        Route::get('/{company}', [\App\Http\Controllers\Admin\CompaniesController::class, 'show'])->name('admin.companies.show');
        Route::get('/{company}/edit', [\App\Http\Controllers\Admin\CompaniesController::class, 'edit'])->name('admin.companies.edit');
        Route::put('/{company}', [\App\Http\Controllers\Admin\CompaniesController::class, 'update'])->name('admin.companies.update');
        Route::delete('/{company}', [\App\Http\Controllers\Admin\CompaniesController::class, 'destroy'])->name('admin.companies.destroy');
        Route::post('/{company}/generate-key', [\App\Http\Controllers\Admin\CompaniesController::class, 'generateKey'])->name('admin.companies.generate-key');
        Route::post('/{company}/keys/{apiKey}/revoke', [\App\Http\Controllers\Admin\CompaniesController::class, 'revokeKey'])->name('admin.companies.revoke-key');
    });

    // Events & Activities Management
    Route::prefix('events')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminEventsController::class, 'index'])->name('admin.events.index');
        Route::get('/create', [\App\Http\Controllers\Admin\AdminEventsController::class, 'create'])->name('admin.events.create');
        Route::post('/', [\App\Http\Controllers\Admin\AdminEventsController::class, 'store'])->name('admin.events.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AdminEventsController::class, 'edit'])->name('admin.events.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AdminEventsController::class, 'update'])->name('admin.events.update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AdminEventsController::class, 'destroy'])->name('admin.events.destroy');
        Route::get('/boundary-challenges', [\App\Http\Controllers\Admin\AdminEventsController::class, 'boundaryChallenges'])->name('admin.events.boundary-challenges');
        Route::post('/boundary-challenges/{id}/approve', [\App\Http\Controllers\Admin\AdminEventsController::class, 'approveChallenge'])->name('admin.events.boundary-challenges.approve');
        Route::post('/boundary-challenges/{id}/reject', [\App\Http\Controllers\Admin\AdminEventsController::class, 'rejectChallenge'])->name('admin.events.boundary-challenges.reject');
        
        Route::get('/{id}/resolve', [\App\Http\Controllers\Admin\AdminEventsController::class, 'showResolveForm'])->name('admin.events.resolve.form');
        Route::post('/{id}/resolve', [\App\Http\Controllers\Admin\AdminEventsController::class, 'resolve'])->name('admin.events.resolve');
        
        // AJAX support for form
        Route::get('/api/features-by-layer', [\App\Http\Controllers\Admin\AdminEventsController::class, 'getFeaturesByLayer'])->name('admin.events.features-by-layer');
    });

    // App Management (Versions & Announcements)
    Route::prefix('app-management')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AppManagementController::class, 'index'])->name('admin.app.dashboard');
        Route::post('/version', [\App\Http\Controllers\Admin\AppManagementController::class, 'storeVersion'])->name('admin.app.version.store');
        Route::post('/version/{id}/toggle', [\App\Http\Controllers\Admin\AppManagementController::class, 'toggleVersion'])->name('admin.app.version.toggle');
        Route::delete('/version/{id}', [\App\Http\Controllers\Admin\AppManagementController::class, 'deleteVersion'])->name('admin.app.version.delete');

        Route::post('/announcement', [\App\Http\Controllers\Admin\AppManagementController::class, 'storeAnnouncement'])->name('admin.app.announcement.store');
        Route::post('/announcement/{id}/toggle', [\App\Http\Controllers\Admin\AppManagementController::class, 'toggleAnnouncement'])->name('admin.app.announcement.toggle');
        Route::delete('/announcement/{id}', [\App\Http\Controllers\Admin\AppManagementController::class, 'deleteAnnouncement'])->name('admin.app.announcement.delete');
    });
});
