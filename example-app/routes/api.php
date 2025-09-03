<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CandidateAuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\CandidateApplicationController;

/*
|--------------------------------------------------------------------------
| API Routes - CV Filtering System
|--------------------------------------------------------------------------
|
| Routes pour le système de filtrage de CV avec IA
| - Authentification des candidats
| - Gestion des utilisateurs (Admin)
| - Gestion des candidatures
|
*/

// Route de test de l'API
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'CV Filtering API is working',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});

// Route publique temporaire pour les utilisateurs (pour le dashboard)
Route::get('/users-public', [App\Http\Controllers\UserController::class, 'index']);

// Route publique temporaire pour les offres (pour le frontend Angular)
Route::get('/job-offers-public', [App\Http\Controllers\JobOfferController::class, 'index']);

// Route ultra-rapide pour le frontend Angular (avec cache Redis)
Route::get('/job-offers-fast', [App\Http\Controllers\JobOfferController::class, 'getFastPublicOffers']);

// Route de test de vitesse (sans base de données)
Route::get('/test-speed', [App\Http\Controllers\JobOfferController::class, 'testSpeed']);

// Route ultra-rapide pour le frontend Angular (avec cache Redis)
Route::get('/job-offers-fast', [App\Http\Controllers\JobOfferController::class, 'getFastPublicOffers']);

/*
|--------------------------------------------------------------------------
| Routes d'authentification publiques
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->name('api.auth.')->group(function () {
    // Inscription et connexion des candidats
    Route::post('register', [CandidateAuthController::class, 'register'])->name('register');
    Route::post('login', [CandidateAuthController::class, 'login'])->name('login');

    // Routes de récupération de mot de passe (existantes)
    Route::post('password/email', [App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::post('password/reset', [App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])
        ->name('password.reset');
    Route::post('password/verify-token', [App\Http\Controllers\Auth\PasswordResetController::class, 'verifyToken'])
        ->name('password.verify');
});

/*
|--------------------------------------------------------------------------
| Routes d'authentification Backend (Admin/RH/Recruteur)
|--------------------------------------------------------------------------
*/
Route::prefix('backend/auth')->name('api.backend.auth.')->group(function () {
    // Inscription et connexion des utilisateurs backend
    Route::post('register', [App\Http\Controllers\Auth\BackendAuthController::class, 'register'])->name('register');
    Route::post('login', [App\Http\Controllers\Auth\BackendAuthController::class, 'login'])->name('login');

    // Routes protégées
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [App\Http\Controllers\Auth\BackendAuthController::class, 'logout'])->name('logout');
        Route::get('profile', [App\Http\Controllers\Auth\BackendAuthController::class, 'profile'])->name('profile');
    });
});

/*
|--------------------------------------------------------------------------
| Routes publiques pour l'inscription
|--------------------------------------------------------------------------
*/
Route::get('public/roles', [App\Http\Controllers\Auth\BackendAuthController::class, 'getAvailableRoles']);

/*
|--------------------------------------------------------------------------
| Routes publiques des offres d'emploi
|--------------------------------------------------------------------------
*/
// Routes publiques pour consulter les offres (pour les candidats)
Route::prefix('job-offers')->name('api.job-offers.')->group(function () {
    Route::get('/', [App\Http\Controllers\JobOfferController::class, 'index'])->name('index');
    Route::get('/{id}', [App\Http\Controllers\JobOfferController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Routes de gestion des rôles et permissions
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->prefix('admin')->group(function () {
    // Gestion des rôles et permissions
    Route::apiResource('roles', App\Http\Controllers\Admin\RolePermissionController::class);
    Route::get('permissions', [App\Http\Controllers\Admin\RolePermissionController::class, 'getPermissions']);
    Route::post('roles/assign', [App\Http\Controllers\Admin\RolePermissionController::class, 'assignRole']);
    Route::post('roles/revoke', [App\Http\Controllers\Admin\RolePermissionController::class, 'revokeRole']);

    // Gestion des offres d'emploi (CRUD complet pour les admins)
    Route::apiResource('job-offers', App\Http\Controllers\JobOfferController::class)->except(['index', 'show']);
});

/*
|--------------------------------------------------------------------------
| Routes d'analyse CV avec OpenAI
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->prefix('cv-analysis')->group(function () {
    // Routes principales d'analyse CV
    Route::get('/', [App\Http\Controllers\CVAnalysisController::class, 'index'])
        ->middleware('permission:ai_analysis.view');

    Route::post('/', [App\Http\Controllers\CVAnalysisController::class, 'store'])
        ->middleware('permission:ai_analysis.trigger');

    Route::get('/{cvAnalysis}', [App\Http\Controllers\CVAnalysisController::class, 'show'])
        ->middleware('permission:ai_analysis.view');

    Route::delete('/{cvAnalysis}', [App\Http\Controllers\CVAnalysisController::class, 'destroy'])
        ->middleware('permission:ai_analysis.delete');

    // Déclencher l'analyse IA
    Route::post('/{cvAnalysis}/analyze', [App\Http\Controllers\CVAnalysisController::class, 'analyze'])
        ->middleware('permission:ai_analysis.trigger');

    // Analyser du texte personnalisé (pour les tests)
    Route::post('/analyze-text', [App\Http\Controllers\CVAnalysisController::class, 'analyzeText'])
        ->middleware('permission:ai_analysis.trigger');

    // Statistiques et rapports
    Route::get('/statistics/overview', [App\Http\Controllers\CVAnalysisController::class, 'statistics'])
        ->middleware('permission:reports.view');

    // Validation de la configuration OpenAI
    Route::get('/openai/validate', [App\Http\Controllers\CVAnalysisController::class, 'validateOpenAI'])
        ->middleware('permission:system.ai_config');
});

/*
|--------------------------------------------------------------------------
| Routes du Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->prefix('dashboard')->group(function () {
    // Statistiques principales
    Route::get('statistics', [App\Http\Controllers\DashboardController::class, 'getStatistics']);

    // Activité récente
    Route::get('recent-activity', [App\Http\Controllers\DashboardController::class, 'getRecentActivity']);

    // Données pour les graphiques
    Route::get('chart-data', [App\Http\Controllers\DashboardController::class, 'getChartData']);
});

/*
|--------------------------------------------------------------------------
| Routes du CV Filtering System
|--------------------------------------------------------------------------
*/

// Route publique pour la soumission de CV (pas d'authentification requise)
Route::post('candidates', [App\Http\Controllers\CandidateController::class, 'store']);

// Routes protégées pour l'administration
Route::middleware('auth:api')->group(function () {
    // Gestion des candidats (admin seulement)
    Route::get('candidates', [App\Http\Controllers\CandidateController::class, 'index']);
    Route::get('candidates/stats', [App\Http\Controllers\CandidateController::class, 'getStats']);
    Route::get('candidates/export', [App\Http\Controllers\CandidateController::class, 'export']);
    Route::get('candidates/{id}', [App\Http\Controllers\CandidateController::class, 'show']);
    Route::put('candidates/{id}/status', [App\Http\Controllers\CandidateController::class, 'updateStatus']);

    // Analyses CV pour le dashboard
    Route::get('cv-analyses', [App\Http\Controllers\CVAnalysisController::class, 'dashboardIndex']);
    Route::post('cv-analyses/trigger-batch', [App\Http\Controllers\CVAnalysisController::class, 'triggerBatchAnalysis']);
    Route::post('cv-analyses/{id}/trigger', [App\Http\Controllers\CVAnalysisController::class, 'triggerSingleAnalysis']);

    // Statut de l'IA
    Route::get('ai/status', [App\Http\Controllers\CVAnalysisController::class, 'checkAIStatus']);

    // Gestion des utilisateurs (admin seulement)
    Route::get('backend/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('backend/users/{id}', [App\Http\Controllers\UserController::class, 'show']);
    Route::put('backend/users/{id}/role', [App\Http\Controllers\UserController::class, 'updateRole']);

    // Rapports et statistiques
    Route::get('reports/general-stats', [App\Http\Controllers\ReportsController::class, 'generalStats']);
    Route::get('reports/candidates-evolution', [App\Http\Controllers\ReportsController::class, 'candidatesEvolution']);
    Route::get('reports/ai-performance', [App\Http\Controllers\ReportsController::class, 'aiPerformance']);
    Route::get('reports/detailed-report', [App\Http\Controllers\ReportsController::class, 'detailedReport']);
});

/*
|--------------------------------------------------------------------------
| Routes protégées par authentification
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Routes d'authentification protégées
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->name('api.auth.')->group(function () {
        Route::post('logout', [CandidateAuthController::class, 'logout'])->name('logout');
        Route::get('profile', [CandidateAuthController::class, 'profile'])->name('profile');
        Route::put('profile', [CandidateAuthController::class, 'updateProfile'])->name('profile.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Routes de gestion des candidatures
    |--------------------------------------------------------------------------
    */
    Route::prefix('applications')->name('api.applications.')->group(function () {
        // Routes pour tous les utilisateurs authentifiés
        Route::get('/', [CandidateApplicationController::class, 'index'])->name('index');
        Route::post('/', [CandidateApplicationController::class, 'store'])->name('store');
        Route::get('/{application}', [CandidateApplicationController::class, 'show'])->name('show');
        Route::delete('/{application}', [CandidateApplicationController::class, 'destroy'])->name('destroy');

        // Téléchargement de fichiers
        Route::get('/{application}/download/{fileType}', [CandidateApplicationController::class, 'downloadFile'])
            ->name('download')
            ->where('fileType', 'cv|cover_letter');

        // Routes admin seulement
        Route::middleware('admin')->group(function () {
            Route::put('/{application}/status', [CandidateApplicationController::class, 'updateStatus'])->name('status.update');
            Route::post('/{application}/ai-analysis', [CandidateApplicationController::class, 'addAiAnalysis'])->name('ai-analysis.store');
            Route::get('/statistics/overview', [CandidateApplicationController::class, 'statistics'])->name('statistics');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Routes d'administration
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('api.admin.')->middleware('admin')->group(function () {

        // Gestion des utilisateurs
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/search', [UserManagementController::class, 'search'])->name('search');
            Route::get('/statistics', [UserManagementController::class, 'statistics'])->name('statistics');
            Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');

            // Actions de blocage/déblocage
            Route::post('/{user}/block', [UserManagementController::class, 'blockUser'])->name('block');
            Route::post('/{user}/unblock', [UserManagementController::class, 'unblockUser'])->name('unblock');
        });
    });
});
