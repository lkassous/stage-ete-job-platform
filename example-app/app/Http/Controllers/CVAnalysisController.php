<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CVAnalysis;
use App\Models\CandidateApplication;
use App\Models\JobPosition;
use App\Models\Candidate;
use App\Services\OpenAIService;
use App\Mail\CVAnalysisCompleted;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class CVAnalysisController extends Controller
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Liste toutes les analyses CV avec pagination et filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CVAnalysis::with(['candidateApplication', 'jobPosition'])
                ->orderBy('created_at', 'desc');

            // Filtres
            if ($request->has('status')) {
                $query->where('analysis_status', $request->status);
            }

            if ($request->has('job_position_id')) {
                $query->where('job_position_id', $request->job_position_id);
            }

            if ($request->has('rating')) {
                $query->where('overall_rating', $request->rating);
            }

            if ($request->has('min_score')) {
                $query->where('job_match_score', '>=', $request->min_score);
            }

            $analyses = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $analyses,
                'summary' => [
                    'total' => CVAnalysis::count(),
                    'completed' => CVAnalysis::where('analysis_status', 'completed')->count(),
                    'pending' => CVAnalysis::where('analysis_status', 'pending')->count(),
                    'processing' => CVAnalysis::where('analysis_status', 'processing')->count(),
                    'failed' => CVAnalysis::where('analysis_status', 'failed')->count(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching CV analyses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des analyses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche une analyse CV spécifique
     */
    public function show(CVAnalysis $cvAnalysis): JsonResponse
    {
        try {
            $cvAnalysis->load(['candidateApplication', 'jobPosition']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $cvAnalysis->id,
                    'candidate_application' => $cvAnalysis->candidateApplication,
                    'job_position' => $cvAnalysis->jobPosition,
                    'analysis_status' => $cvAnalysis->analysis_status,
                    'profile_summary' => $cvAnalysis->profile_summary,
                    'key_skills' => $cvAnalysis->key_skills,
                    'education' => $cvAnalysis->education,
                    'experience' => $cvAnalysis->experience,
                    'languages' => $cvAnalysis->languages,
                    'strengths' => $cvAnalysis->strengths,
                    'weaknesses' => $cvAnalysis->weaknesses,
                    'job_match_score' => $cvAnalysis->job_match_score,
                    'job_match_analysis' => $cvAnalysis->job_match_analysis,
                    'recommendations' => $cvAnalysis->recommendations,
                    'overall_rating' => $cvAnalysis->overall_rating,
                    'next_steps' => $cvAnalysis->next_steps,
                    'tokens_used' => $cvAnalysis->tokens_used,
                    'cost_estimate' => $cvAnalysis->cost_estimate,
                    'analyzed_at' => $cvAnalysis->analyzed_at,
                    'created_at' => $cvAnalysis->created_at,
                    'updated_at' => $cvAnalysis->updated_at,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching CV analysis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'analyse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle analyse CV pour une candidature
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'candidate_application_id' => 'required|exists:candidate_applications,id',
                'job_position_id' => 'nullable|exists:job_positions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier qu'une analyse n'existe pas déjà pour cette candidature
            $existingAnalysis = CVAnalysis::where('candidate_application_id', $request->candidate_application_id)
                ->where('job_position_id', $request->job_position_id)
                ->first();

            if ($existingAnalysis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une analyse existe déjà pour cette candidature et ce poste'
                ], 422);
            }

            // Créer l'analyse
            $analysis = CVAnalysis::create([
                'candidate_application_id' => $request->candidate_application_id,
                'job_position_id' => $request->job_position_id,
                'analysis_status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Analyse CV créée avec succès',
                'data' => $analysis
            ], 201);

        } catch (Exception $e) {
            Log::error('Error creating CV analysis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'analyse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déclencher l'analyse IA pour un CV
     */
    public function analyze(CVAnalysis $cvAnalysis): JsonResponse
    {
        try {
            // Vérifier que l'analyse n'est pas déjà en cours ou terminée
            if ($cvAnalysis->analysis_status === 'processing') {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'analyse est déjà en cours de traitement'
                ], 422);
            }

            // Déclencher l'analyse IA
            $success = $cvAnalysis->processWithAI();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Analyse IA déclenchée avec succès',
                    'data' => $cvAnalysis->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du déclenchement de l\'analyse IA'
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Error triggering AI analysis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déclenchement de l\'analyse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyser un CV avec du texte personnalisé (pour les tests)
     */
    public function analyzeText(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cv_text' => 'required|string|min:50',
                'cover_letter_text' => 'nullable|string',
                'job_description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Analyser directement avec OpenAI
            $result = $this->openAIService->analyzeCv(
                $request->cv_text,
                $request->cover_letter_text ?? '',
                $request->job_description ?? ''
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Analyse terminée avec succès',
                    'data' => [
                        'analysis' => $result['analysis'],
                        'usage' => $result['usage']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'analyse',
                    'error' => $result['error']
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Error analyzing text: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse du texte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une analyse CV
     */
    public function destroy(CVAnalysis $cvAnalysis): JsonResponse
    {
        try {
            $cvAnalysis->delete();

            return response()->json([
                'success' => true,
                'message' => 'Analyse supprimée avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting CV analysis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'analyse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des analyses CV
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_analyses' => CVAnalysis::count(),
                'completed_analyses' => CVAnalysis::where('analysis_status', 'completed')->count(),
                'pending_analyses' => CVAnalysis::where('analysis_status', 'pending')->count(),
                'processing_analyses' => CVAnalysis::where('analysis_status', 'processing')->count(),
                'failed_analyses' => CVAnalysis::where('analysis_status', 'failed')->count(),
                'average_match_score' => CVAnalysis::where('analysis_status', 'completed')
                    ->whereNotNull('job_match_score')
                    ->avg('job_match_score'),
                'total_tokens_used' => CVAnalysis::sum('tokens_used'),
                'total_cost_estimate' => CVAnalysis::sum('cost_estimate'),
                'rating_distribution' => CVAnalysis::where('analysis_status', 'completed')
                    ->whereNotNull('overall_rating')
                    ->selectRaw('overall_rating, COUNT(*) as count')
                    ->groupBy('overall_rating')
                    ->pluck('count', 'overall_rating'),
                'analyses_by_month' => CVAnalysis::selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as count')
                    ->groupBy('month')
                    ->orderBy('month', 'desc')
                    ->limit(12)
                    ->pluck('count', 'month'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching CV analysis statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider la configuration OpenAI
     */
    public function validateOpenAI(): JsonResponse
    {
        try {
            $result = $this->openAIService->validateConfiguration();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? $result['message'] : $result['error']
            ], $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error validating OpenAI configuration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation de la configuration OpenAI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister toutes les analyses CV pour le dashboard
     */
    public function dashboardIndex(): JsonResponse
    {
        try {
            $analyses = CVAnalysis::with(['candidate', 'jobPosition'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($analysis) {
                    return [
                        'id' => $analysis->id,
                        'candidate_name' => $analysis->candidate ?
                            $analysis->candidate->prenom . ' ' . $analysis->candidate->nom : 'Candidat inconnu',
                        'candidate_email' => $analysis->candidate ? $analysis->candidate->email : null,
                        'job_position_title' => $analysis->jobPosition ? $analysis->jobPosition->title : 'Poste non spécifié',
                        'analysis_status' => $analysis->analysis_status,
                        'job_match_score' => $analysis->job_match_score,
                        'profile_summary' => $analysis->profile_summary,
                        'key_skills' => $analysis->key_skills,
                        'education' => $analysis->education,
                        'experience' => $analysis->experience,
                        'job_match_analysis' => $analysis->job_match_analysis,
                        'overall_rating' => $analysis->overall_rating,
                        'tokens_used' => $analysis->tokens_used,
                        'cost_estimate' => $analysis->cost_estimate,
                        'analyzed_at' => $analysis->analyzed_at,
                        'created_at' => $analysis->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $analyses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des analyses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déclencher l'analyse IA pour toutes les analyses en attente
     */
    public function triggerBatchAnalysis(): JsonResponse
    {
        try {
            $pendingAnalyses = CVAnalysis::where('analysis_status', 'pending')
                ->with('candidate')
                ->get();

            if ($pendingAnalyses->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aucune analyse en attente',
                    'data' => ['count' => 0]
                ]);
            }

            $processedCount = 0;
            foreach ($pendingAnalyses as $analysis) {
                try {
                    // Marquer comme en cours
                    $analysis->update(['analysis_status' => 'processing']);

                    // Simuler l'analyse IA (remplacer par vraie intégration OpenAI)
                    $this->simulateAIAnalysis($analysis);

                    $processedCount++;
                } catch (\Exception $e) {
                    // Marquer comme échoué
                    $analysis->update([
                        'analysis_status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Analyse IA lancée pour {$processedCount} CV(s)",
                'data' => ['count' => $processedCount]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du lancement de l\'analyse batch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déclencher l'analyse IA pour une analyse spécifique
     */
    public function triggerSingleAnalysis($id): JsonResponse
    {
        try {
            $analysis = CVAnalysis::with('candidate')->findOrFail($id);

            if ($analysis->analysis_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette analyse n\'est pas en attente'
                ], 400);
            }

            // Marquer comme en cours
            $analysis->update(['analysis_status' => 'processing']);

            // Simuler l'analyse IA
            $this->simulateAIAnalysis($analysis);

            return response()->json([
                'success' => true,
                'message' => 'Analyse IA lancée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du lancement de l\'analyse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier le statut de l'API IA
     */
    public function checkAIStatus(): JsonResponse
    {
        try {
            // Simuler la vérification du statut OpenAI
            return response()->json([
                'success' => true,
                'data' => [
                    'connected' => true,
                    'api_key_valid' => true,
                    'last_check' => now()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut IA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simuler l'analyse IA (à remplacer par l'intégration OpenAI réelle)
     */
    private function simulateAIAnalysis(CVAnalysis $analysis)
    {
        // Simuler un délai d'analyse
        sleep(1);

        $candidate = $analysis->candidate;
        $candidateName = $candidate ? $candidate->prenom . ' ' . $candidate->nom : 'Candidat';

        // Données simulées d'analyse IA
        $simulatedData = [
            'profile_summary' => "Profil de {$candidateName} analysé par IA. Candidat avec une expérience pertinente et des compétences techniques solides.",
            'key_skills' => json_encode([
                'Communication',
                'Travail en équipe',
                'Résolution de problèmes',
                'Adaptabilité',
                'Leadership'
            ]),
            'education' => json_encode([
                [
                    'degree' => 'Master/Licence',
                    'school' => 'Université/École',
                    'year' => '2020-2022'
                ]
            ]),
            'experience' => json_encode([
                [
                    'title' => 'Poste précédent',
                    'company' => 'Entreprise',
                    'years' => '2-3 ans'
                ]
            ]),
            'job_match_score' => rand(60, 95),
            'job_match_analysis' => 'Analyse de correspondance générée par IA. Le candidat présente un bon potentiel pour le poste avec des compétences alignées sur les exigences.',
            'overall_rating' => ['A', 'B', 'C'][rand(0, 2)],
            'analysis_status' => 'completed',
            'analyzed_at' => now(),
            'tokens_used' => rand(800, 1500),
            'cost_estimate' => round(rand(15, 35) / 1000, 4) // $0.015 - $0.035
        ];

        $analysis->update($simulatedData);

        // Envoyer l'email d'analyse terminée
        try {
            $candidate = Candidate::find($analysis->candidate_id);
            if ($candidate) {
                Mail::to($candidate->email)->send(new CVAnalysisCompleted($candidate, $analysis));
                Log::info('Email d\'analyse terminée envoyé à: ' . $candidate->email);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email d\'analyse terminée: ' . $e->getMessage());
        }
    }
}
