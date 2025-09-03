<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Envoyer un lien de réinitialisation de mot de passe
     */
    public function sendResetLinkEmail(Request $request)
    {
        try {
            // Validation de l'email
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Envoyer le lien de réinitialisation
            $status = Password::sendResetLink(
                $request->only('email'),
                function ($user, $token) {
                    // Callback optionnel pour personnaliser l'envoi
                }
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.',
                    'data' => [
                        'email' => $request->email,
                        'status' => 'sent'
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'envoyer le lien de réinitialisation. Veuillez réessayer.',
                'data' => [
                    'status' => $status
                ]
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de l\'envoi du lien de réinitialisation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function reset(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Réinitialiser le mot de passe
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Votre mot de passe a été réinitialisé avec succès.',
                    'data' => [
                        'status' => 'reset_successful'
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $this->getResetErrorMessage($status),
                'data' => [
                    'status' => $status
                ]
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la réinitialisation du mot de passe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier la validité d'un token de réinitialisation
     */
    public function verifyToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token ou email invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si le token est valide
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }

            // Vérifier le token avec le broker de mots de passe
            $broker = Password::broker();
            $tokenExists = $broker->tokenExists($user, $request->token);

            return response()->json([
                'success' => $tokenExists,
                'message' => $tokenExists ? 'Token valide' : 'Token invalide ou expiré',
                'data' => [
                    'token_valid' => $tokenExists,
                    'email' => $request->email
                ]
            ], $tokenExists ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le message d'erreur approprié pour le statut de réinitialisation
     */
    private function getResetErrorMessage($status)
    {
        switch ($status) {
            case Password::INVALID_TOKEN:
                return 'Le token de réinitialisation est invalide ou a expiré.';
            case Password::INVALID_USER:
                return 'Aucun utilisateur trouvé avec cette adresse email.';
            case Password::RESET_THROTTLED:
                return 'Trop de tentatives de réinitialisation. Veuillez attendre avant de réessayer.';
            default:
                return 'Erreur lors de la réinitialisation du mot de passe.';
        }
    }
}
