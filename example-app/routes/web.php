<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordResetController;

Route::get('/', function () {
    return view('welcome');
});

// Route pour servir la page forgot-password.html
Route::get('/auth/forgot-password.html', function () {
    return response()->file(public_path('auth/forgot-password.html'));
});

// Route pour servir la page forgot-password.html
Route::get('/auth/forgot-password.html', function () {
    return response()->file(public_path('auth/forgot-password.html'));
});

// Routes pour la réinitialisation de mot de passe
Route::prefix('password')->group(function () {
    // Formulaire de demande de réinitialisation
    Route::get('/reset', function () {
        return view('auth.passwords.email');
    })->name('password.request');

    // Envoyer le lien de réinitialisation
    Route::post('/email', [PasswordResetController::class, 'sendResetLinkEmail'])
         ->name('password.email');

    // Formulaire de réinitialisation avec token
    Route::get('/reset/{token}', function ($token) {
        return view('auth.passwords.reset', ['token' => $token]);
    })->name('password.reset');

    // Traitement de la réinitialisation
    Route::post('/reset', [PasswordResetController::class, 'reset'])
         ->name('password.update');

    // Vérification de token (API)
    Route::post('/verify-token', [PasswordResetController::class, 'verifyToken'])
         ->name('password.verify');
});

// Route de test pour reset password (sans CSRF)
Route::post('/test-password-reset', function (Illuminate\Http\Request $request) {
    try {
        $request->validate(['email' => 'required|email']);

        $status = Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));

        if ($status === Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Email de réinitialisation envoyé avec succès !',
                'status' => $status
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi de l\'email',
            'status' => $status
        ], 400);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur: ' . $e->getMessage()
        ], 500);
    }
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
