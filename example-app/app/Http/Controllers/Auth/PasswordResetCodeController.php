<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetCode;
use App\Mail\PasswordResetCodeMail;

class PasswordResetCodeController extends Controller
{
    /**
     * Envoyer un code de réinitialisation par email
     */
    public function sendResetCode(Request $request)
    {
        try {
            // Validation de l'email
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ], [
                'email.required' => 'L\'adresse email est requise.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.exists' => 'Aucun compte n\'est associé à cette adresse email.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si l'utilisateur existe
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé avec cette adresse email.'
                ], 404);
            }

            // Générer un nouveau code
            $resetCode = PasswordResetCode::generateCode($request->email);

            // Envoyer l'email avec le code
            Mail::to($request->email)->send(new PasswordResetCodeMail($user, $resetCode->code));

            return response()->json([
                'success' => true,
                'message' => 'Un code de réinitialisation a été envoyé à votre adresse email.',
                'data' => [
                    'email' => $request->email,
                    'expires_in_minutes' => 15
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code de réinitialisation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier un code de réinitialisation
     */
    public function verifyCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required|string|size:6',
            ], [
                'email.required' => 'L\'adresse email est requise.',
                'email.email' => 'L\'adresse email doit être valide.',
                'code.required' => 'Le code de vérification est requis.',
                'code.size' => 'Le code doit contenir exactement 6 caractères.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Chercher le code valide
            $resetCode = PasswordResetCode::findValidCode($request->email, $request->code);

            if (!$resetCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou expiré. Veuillez demander un nouveau code.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Code valide. Vous pouvez maintenant réinitialiser votre mot de passe.',
                'data' => [
                    'email' => $request->email,
                    'code_valid' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du code.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe avec un code
     */
    public function resetPasswordWithCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required|string|size:6',
                'password' => 'required|min:8|confirmed',
            ], [
                'email.required' => 'L\'adresse email est requise.',
                'email.email' => 'L\'adresse email doit être valide.',
                'code.required' => 'Le code de vérification est requis.',
                'code.size' => 'Le code doit contenir exactement 6 caractères.',
                'password.required' => 'Le nouveau mot de passe est requis.',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Chercher le code valide
            $resetCode = PasswordResetCode::findValidCode($request->email, $request->code);

            if (!$resetCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou expiré. Veuillez demander un nouveau code.'
                ], 400);
            }

            // Trouver l'utilisateur
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            }

            // Réinitialiser le mot de passe
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Marquer le code comme utilisé
            $resetCode->markAsUsed();

            return response()->json([
                'success' => true,
                'message' => 'Votre mot de passe a été réinitialisé avec succès.',
                'data' => [
                    'email' => $request->email,
                    'password_reset' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
