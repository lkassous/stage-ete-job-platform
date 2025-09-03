<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplication;
use App\Models\JobPosition;
use App\Models\CVAnalysis;
use App\Models\User;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    /**
     * Obtenir les statistiques principales du dashboard
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Statistiques générales pour CV Filtering System
            $totalCandidates = Candidate::count() ?? 0; // Utiliser la table candidates
            $analyzedCandidates = CVAnalysis::where('analysis_status', 'completed')->count() ?? 0;
            $activeJobs = JobPosition::where('status', 'active')->count() ?? 0;
            $pendingAnalyses = CVAnalysis::where('analysis_status', 'pending')->count() ?? 0;

            // Statistiques du jour pour CV Filtering
            $todayCandidates = Candidate::whereDate('created_at', Carbon::today())->count();
            $todayAnalyses = CVAnalysis::whereDate('analyzed_at', Carbon::today())->count();

            // Taux de correspondance moyen avec gestion des erreurs
            $averageMatchScore = 0;
            try {
                $averageMatchScore = CVAnalysis::where('analysis_status', 'completed')
                    ->whereNotNull('job_match_score')
                    ->avg('job_match_score') ?? 0;
            } catch (\Exception $e) {
                // Ignorer les erreurs et utiliser 0
            }

            // Évolution par rapport au mois précédent pour CV Filtering
            $lastMonthCandidates = 0;
            $currentMonthCandidates = 0;
            try {
                $lastMonthCandidates = Candidate::whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count() ?? 0;

                $currentMonthCandidates = Candidate::whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()
                ])->count() ?? 0;
            } catch (\Exception $e) {
                // Ignorer les erreurs
            }

            $candidatesGrowth = $lastMonthCandidates > 0
                ? round((($currentMonthCandidates - $lastMonthCandidates) / $lastMonthCandidates) * 100, 1)
                : ($currentMonthCandidates > 0 ? 100 : 0);

            // Évolution des analyses avec gestion des erreurs
            $lastMonthAnalyses = 0;
            $currentMonthAnalyses = 0;
            try {
                $lastMonthAnalyses = CVAnalysis::where('analysis_status', 'completed')
                    ->whereBetween('analyzed_at', [
                        Carbon::now()->subMonth()->startOfMonth(),
                        Carbon::now()->subMonth()->endOfMonth()
                    ])->count() ?? 0;

                $currentMonthAnalyses = CVAnalysis::where('analysis_status', 'completed')
                    ->whereBetween('analyzed_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()
                    ])->count() ?? 0;
            } catch (\Exception $e) {
                // Ignorer les erreurs
            }

            $analysesGrowth = $lastMonthAnalyses > 0
                ? round((($currentMonthAnalyses - $lastMonthAnalyses) / $lastMonthAnalyses) * 100, 1)
                : ($currentMonthAnalyses > 0 ? 100 : 0);

            return response()->json([
                'success' => true,
                'data' => [
                    'main_stats' => [
                        'total_candidates' => [
                            'value' => $totalCandidates,
                            'growth' => $candidatesGrowth,
                            'label' => 'Total des candidatures'
                        ],
                        'analyzed_candidates' => [
                            'value' => $analyzedCandidates,
                            'growth' => $analysesGrowth,
                            'label' => 'Analyses IA terminées'
                        ],
                        'active_jobs' => [
                            'value' => $activeJobs,
                            'growth' => 0, // Calculer si nécessaire
                            'label' => 'Postes actifs'
                        ],
                        'match_rate' => [
                            'value' => round($averageMatchScore ?? 0, 1) . '%',
                            'growth' => 0, // Calculer si nécessaire
                            'label' => 'Taux de correspondance moyen'
                        ]
                    ],
                    'quick_stats' => [
                        'today_candidates' => $todayCandidates,
                        'pending_analyses' => $pendingAnalyses,
                        'today_analyses' => $todayAnalyses,
                        'success_rate' => round(($analyzedCandidates / max($totalCandidates, 1)) * 100, 1) . '%'
                    ],
                    'ai_stats' => [
                        'total_tokens_used' => CVAnalysis::sum('tokens_used') ?? 0,
                        'total_cost' => CVAnalysis::sum('cost_estimate') ?? 0,
                        'average_processing_time' => '2.3s', // Simulé
                        'api_status' => 'connected'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'activité récente
     */
    public function getRecentActivity(): JsonResponse
    {
        try {
            $activities = [];

            // Dernières candidatures CV (5 dernières) avec gestion des erreurs
            try {
                $recentCandidates = Candidate::orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($recentCandidates as $candidate) {
                    $userName = $candidate->prenom . ' ' . $candidate->nom;
                    $userInitials = strtoupper(
                        substr($candidate->prenom ?? 'C', 0, 1) .
                        substr($candidate->nom ?? 'A', 0, 1)
                    );

                    $activities[] = [
                        'type' => 'candidate',
                        'title' => 'CV soumis',
                        'description' => $userName . ' - ' . ucfirst($candidate->status),
                        'avatar' => $userInitials,
                        'time' => $candidate->created_at->diffForHumans(),
                        'created_at' => $candidate->created_at
                    ];
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs de candidatures
            }

            // Dernières analyses IA (5 dernières) avec gestion des erreurs
            try {
                $recentAnalyses = CVAnalysis::where('analysis_status', 'completed')
                    ->orderBy('analyzed_at', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($recentAnalyses as $analysis) {
                    $activities[] = [
                        'type' => 'analysis',
                        'title' => 'Analyse IA terminée',
                        'description' => 'Score: ' . ($analysis->job_match_score ?? 'N/A') . '/100',
                        'avatar' => 'AI',
                        'time' => $analysis->analyzed_at ? $analysis->analyzed_at->diffForHumans() : 'Récemment',
                        'created_at' => $analysis->analyzed_at ?? $analysis->created_at
                    ];
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs d'analyses
            }

            // Derniers postes créés (3 derniers) avec gestion des erreurs
            try {
                $recentJobs = JobPosition::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();

                foreach ($recentJobs as $job) {
                    $activities[] = [
                        'type' => 'job',
                        'title' => 'Nouveau poste créé',
                        'description' => $job->title,
                        'avatar' => 'HR',
                        'time' => $job->created_at->diffForHumans(),
                        'created_at' => $job->created_at
                    ];
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs de postes
            }

            // Trier par date de création (plus récent en premier) avec gestion des erreurs
            if (!empty($activities)) {
                try {
                    usort($activities, function($a, $b) {
                        return $b['created_at'] <=> $a['created_at'];
                    });

                    // Garder seulement les 10 plus récents
                    $activities = array_slice($activities, 0, 10);
                } catch (\Exception $e) {
                    // En cas d'erreur de tri, garder les activités telles quelles
                }
            }

            // Si aucune activité, créer des exemples
            if (empty($activities)) {
                $activities = [
                    [
                        'type' => 'system',
                        'title' => 'Système initialisé',
                        'description' => 'Dashboard prêt à l\'utilisation',
                        'avatar' => 'SY',
                        'time' => 'Il y a quelques minutes',
                        'created_at' => now()
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'activité récente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les données pour les graphiques
     */
    public function getChartData(): JsonResponse
    {
        try {
            // Candidatures par mois (6 derniers mois)
            $candidatesChart = CandidateApplication::selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as count')
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'month' => Carbon::parse($item->month)->format('M Y'),
                        'count' => $item->count
                    ];
                });

            // Analyses par statut
            $analysesChart = CVAnalysis::selectRaw('analysis_status, COUNT(*) as count')
                ->groupBy('analysis_status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->analysis_status,
                        'count' => $item->count,
                        'label' => $this->getStatusLabel($item->analysis_status)
                    ];
                });

            // Distribution des scores de correspondance
            $scoresChart = CVAnalysis::selectRaw('
                CASE
                    WHEN job_match_score >= 90 THEN \'Excellent (90-100)\'
                    WHEN job_match_score >= 80 THEN \'Très bon (80-89)\'
                    WHEN job_match_score >= 70 THEN \'Bon (70-79)\'
                    WHEN job_match_score >= 60 THEN \'Moyen (60-69)\'
                    ELSE \'Faible (<60)\'
                END as score_range,
                COUNT(*) as count
            ')
                ->where('analysis_status', 'completed')
                ->whereNotNull('job_match_score')
                ->groupBy('score_range')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'candidates_by_month' => $candidatesChart,
                    'analyses_by_status' => $analysesChart,
                    'scores_distribution' => $scoresChart
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données graphiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le label d'un statut d'analyse
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            default => $status
        };
    }
}
