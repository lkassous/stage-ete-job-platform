<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CandidateApplicationController extends Controller
{
    /**
     * Middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Soumettre une nouvelle candidature
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cv_file' => 'required|file|mimes:pdf|max:5120', // 5MB max
                'cover_letter' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Vérifier que l'utilisateur est un candidat
            if ($user->user_type !== 'candidate') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les candidats peuvent soumettre des candidatures'
                ], 403);
            }

            // Vérifier que l'utilisateur est actif
            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte est suspendu. Vous ne pouvez pas soumettre de candidature.'
                ], 403);
            }

            // Stocker les fichiers
            $cvPath = $request->file('cv_file')->store('cv_files', 'public');
            $coverLetterPath = null;

            if ($request->hasFile('cover_letter')) {
                $coverLetterPath = $request->file('cover_letter')->store('cover_letters', 'public');
            }

            // Créer la candidature
            $application = CandidateApplication::create([
                'user_id' => $user->id,
                'cv_file_path' => $cvPath,
                'cover_letter_path' => $coverLetterPath,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Candidature soumise avec succès',
                'data' => [
                    'application' => $application->load('user')
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de la candidature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les candidatures de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->user_type === 'candidate') {
                // Pour les candidats : leurs propres candidatures
                $applications = $user->candidateApplications()
                    ->orderBy('submitted_at', 'desc')
                    ->paginate(10);
            } else {
                // Pour les admins : toutes les candidatures
                $query = CandidateApplication::with('user');

                // Filtres pour les admins
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }

                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }

                if ($request->filled('date_from')) {
                    $query->where('submitted_at', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $query->where('submitted_at', '<=', $request->date_to);
                }

                $applications = $query->orderBy('submitted_at', 'desc')->paginate(15);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'applications' => $applications->items(),
                    'pagination' => [
                        'current_page' => $applications->currentPage(),
                        'last_page' => $applications->lastPage(),
                        'per_page' => $applications->perPage(),
                        'total' => $applications->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des candidatures',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les détails d'une candidature
     */
    public function show(CandidateApplication $application): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier les permissions
            if ($user->user_type === 'candidate' && $application->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez voir que vos propres candidatures'
                ], 403);
            }

            $application->load('user');

            return response()->json([
                'success' => true,
                'data' => [
                    'application' => $application
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la candidature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut d'une candidature (Admin seulement)
     */
    public function updateStatus(Request $request, CandidateApplication $application): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est admin
            if ($user->user_type !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent modifier le statut des candidatures'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,processing,analyzed,rejected',
                'admin_notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
            ];

            if ($request->status === 'analyzed') {
                $updateData['analyzed_at'] = now();
            }

            $application->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Statut de la candidature mis à jour avec succès',
                'data' => [
                    'application' => $application->fresh()->load('user')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter l'analyse IA à une candidature (Admin seulement)
     */
    public function addAiAnalysis(Request $request, CandidateApplication $application): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est admin
            if ($user->user_type !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent ajouter une analyse IA'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'ai_analysis' => 'required|array',
                'ai_analysis.profile_summary' => 'required|string',
                'ai_analysis.key_skills' => 'required|array',
                'ai_analysis.education' => 'required|string',
                'ai_analysis.experience' => 'required|string',
                'ai_analysis.suitability_score' => 'required|numeric|min:0|max:100',
                'ai_analysis.recommendations' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $application->update([
                'ai_analysis' => $request->ai_analysis,
                'status' => 'analyzed',
                'analyzed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Analyse IA ajoutée avec succès',
                'data' => [
                    'application' => $application->fresh()->load('user')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de l\'analyse IA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Télécharger un fichier de candidature
     */
    public function downloadFile(CandidateApplication $application, string $fileType): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier les permissions
            if ($user->user_type === 'candidate' && $application->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez télécharger que vos propres fichiers'
                ], 403);
            }

            $filePath = null;
            $fileName = null;

            switch ($fileType) {
                case 'cv':
                    $filePath = $application->cv_file_path;
                    $fileName = 'CV_' . $application->user->name . '.pdf';
                    break;
                case 'cover_letter':
                    $filePath = $application->cover_letter_path;
                    $fileName = 'Lettre_motivation_' . $application->user->name . '.pdf';
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Type de fichier invalide'
                    ], 400);
            }

            if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé'
                ], 404);
            }

            return response()->download(
                Storage::disk('public')->path($filePath),
                $fileName
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du fichier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une candidature
     */
    public function destroy(CandidateApplication $application): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier les permissions
            if ($user->user_type === 'candidate' && $application->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez supprimer que vos propres candidatures'
                ], 403);
            }

            // Supprimer les fichiers du stockage
            if ($application->cv_file_path) {
                Storage::disk('public')->delete($application->cv_file_path);
            }

            if ($application->cover_letter_path) {
                Storage::disk('public')->delete($application->cover_letter_path);
            }

            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Candidature supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la candidature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des candidatures (Admin seulement)
     */
    public function statistics(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est admin
            if ($user->user_type !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent voir les statistiques'
                ], 403);
            }

            $stats = [
                'total_applications' => CandidateApplication::count(),
                'pending_applications' => CandidateApplication::where('status', 'pending')->count(),
                'processing_applications' => CandidateApplication::where('status', 'processing')->count(),
                'analyzed_applications' => CandidateApplication::where('status', 'analyzed')->count(),
                'rejected_applications' => CandidateApplication::where('status', 'rejected')->count(),
                'applications_this_week' => CandidateApplication::where('submitted_at', '>=', now()->subWeek())->count(),
                'applications_this_month' => CandidateApplication::where('submitted_at', '>=', now()->subMonth())->count(),
                'applications_by_month' => CandidateApplication::selectRaw('DATE_TRUNC(\'month\', submitted_at) as month, COUNT(*) as count')
                    ->where('submitted_at', '>=', now()->subMonths(12))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
