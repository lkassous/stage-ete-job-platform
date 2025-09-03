<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CVAnalysis;
use App\Mail\CandidateSubmissionConfirmation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class CandidateController extends Controller
{
    /**
     * Soumettre un nouveau CV
     */
    public function store(Request $request): JsonResponse
    {
        // Log pour debug
        \Log::info('=== SOUMISSION CV ===');
        \Log::info('Request data:', $request->all());
        \Log::info('Files:', $request->allFiles());

        $validator = Validator::make($request->all(), [
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email',
            'telephone' => 'required|string|max:20',
            'linkedin_url' => 'nullable|url',
            'cv_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'lettre_motivation_file' => 'required|file|mimes:pdf|max:10240',
            'job_offer_id' => 'required|exists:job_offers,id'
        ], [
            'prenom.required' => 'Le prénom est obligatoire',
            'nom.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'telephone.required' => 'Le téléphone est obligatoire',
            'cv_file.required' => 'Le CV est obligatoire',
            'cv_file.mimes' => 'Le CV doit être un fichier PDF',
            'lettre_motivation_file.required' => 'La lettre de motivation est obligatoire',
            'lettre_motivation_file.mimes' => 'La lettre de motivation doit être un fichier PDF',
            'job_offer_id.required' => 'L\'offre d\'emploi est obligatoire',
            'job_offer_id.exists' => 'L\'offre d\'emploi sélectionnée n\'existe pas'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Sauvegarder les fichiers
            $cvPath = $request->file('cv_file')->store('cvs', 'public');
            $coverLetterPath = $request->hasFile('lettre_motivation_file')
                ? $request->file('lettre_motivation_file')->store('cover_letters', 'public')
                : null;

            // Créer le candidat
            $candidate = Candidate::create([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'linkedin_url' => $request->linkedin_url,
                'cv_path' => $cvPath,
                'cover_letter_path' => $coverLetterPath,
                'status' => 'pending',
                'submitted_at' => now(),
                'job_offer_id' => $request->job_offer_id,
                'notes' => 'CV soumis via le frontend Angular'
            ]);

            // Envoyer l'email de confirmation
            try {
                Mail::to($candidate->email)->send(new CandidateSubmissionConfirmation($candidate));
                \Log::info('Email de confirmation envoyé à: ' . $candidate->email);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'envoi de l\'email de confirmation: ' . $e->getMessage());
            }

            // Créer une analyse CV en attente (avec gestion d'erreur)
            try {
                CVAnalysis::create([
                    'candidate_id' => $candidate->id,
                    'job_position_id' => 1, // Position par défaut
                    'analysis_status' => 'pending'
                ]);
                \Log::info('Analyse CV créée pour le candidat ID: ' . $candidate->id);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la création de l\'analyse CV: ' . $e->getMessage());
                // Ne pas faire échouer la soumission si l'analyse ne peut pas être créée
            }

            return response()->json([
                'success' => true,
                'message' => 'CV soumis avec succès',
                'data' => [
                    'candidate_id' => $candidate->id,
                    'status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission du CV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister tous les candidats avec pagination et filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Candidate::with(['cvAnalyses', 'jobOffer']);

            // Filtres
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('job_offer_id') && $request->job_offer_id !== 'all') {
                $query->where('job_offer_id', $request->job_offer_id);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%");
                });
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $candidates = $query->paginate($perPage);

            // Statistiques
            $stats = [
                'total' => Candidate::count(),
                'pending' => Candidate::where('status', 'pending')->count(),
                'reviewed' => Candidate::where('status', 'reviewed')->count(),
                'accepted' => Candidate::where('status', 'accepted')->count(),
                'rejected' => Candidate::where('status', 'rejected')->count(),
                'today' => Candidate::whereDate('created_at', today())->count(),
                'this_week' => Candidate::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => Candidate::whereMonth('created_at', now()->month)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $candidates->items(),
                'pagination' => [
                    'current_page' => $candidates->currentPage(),
                    'last_page' => $candidates->lastPage(),
                    'per_page' => $candidates->perPage(),
                    'total' => $candidates->total(),
                    'from' => $candidates->firstItem(),
                    'to' => $candidates->lastItem(),
                ],
                'stats' => $stats,
                'filters' => [
                    'status' => $request->get('status', 'all'),
                    'job_offer_id' => $request->get('job_offer_id', 'all'),
                    'search' => $request->get('search', ''),
                    'date_from' => $request->get('date_from', ''),
                    'date_to' => $request->get('date_to', ''),
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des candidats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Voir un candidat spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $candidate = Candidate::with('cvAnalyses.jobPosition')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $candidate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Candidat non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour le statut d'un candidat
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,reviewing,interviewed,accepted,rejected',
                'note' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $candidate = Candidate::findOrFail($id);

            $updateData = [
                'status' => $request->status,
                'status_updated_at' => now()
            ];

            // Ajouter la note si fournie
            if ($request->note) {
                $currentNotes = $candidate->notes ? $candidate->notes . "\n\n" : '';
                $updateData['notes'] = $currentNotes . '[' . now()->format('d/m/Y H:i') . '] Changement de statut vers "' . $request->status . '": ' . $request->note;
            }

            $candidate->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => $candidate
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
     * Obtenir les statistiques détaillées pour le dashboard
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'overview' => [
                    'total_candidates' => Candidate::count(),
                    'pending_review' => Candidate::where('status', 'pending')->count(),
                    'under_review' => Candidate::where('status', 'reviewed')->count(),
                    'accepted' => Candidate::where('status', 'accepted')->count(),
                    'rejected' => Candidate::where('status', 'rejected')->count(),
                ],
                'timeline' => [
                    'today' => Candidate::whereDate('created_at', today())->count(),
                    'yesterday' => Candidate::whereDate('created_at', today()->subDay())->count(),
                    'this_week' => Candidate::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'last_week' => Candidate::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count(),
                    'this_month' => Candidate::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                    'last_month' => Candidate::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count(),
                ],
                'by_job_offer' => Candidate::selectRaw('job_offer_id, job_offers.title, COUNT(*) as count')
                    ->leftJoin('job_offers', 'candidates.job_offer_id', '=', 'job_offers.id')
                    ->groupBy('job_offer_id', 'job_offers.title')
                    ->get(),
                'recent_activity' => Candidate::with(['jobOffer', 'cvAnalyses'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'analysis_stats' => [
                    'completed' => CVAnalysis::where('analysis_status', 'completed')->count(),
                    'pending' => CVAnalysis::where('analysis_status', 'pending')->count(),
                    'failed' => CVAnalysis::where('analysis_status', 'failed')->count(),
                    'avg_score' => CVAnalysis::where('analysis_status', 'completed')->avg('job_match_score'),
                ]
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

    /**
     * Exporter les candidats en CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Candidate::with(['cvAnalyses', 'jobOffer']);

            // Appliquer les mêmes filtres que pour l'index
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('job_offer_id') && $request->job_offer_id !== 'all') {
                $query->where('job_offer_id', $request->job_offer_id);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%");
                });
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $candidates = $query->orderBy('created_at', 'desc')->get();

            // Générer le CSV
            $csvData = [];
            $csvData[] = [
                'ID',
                'Prénom',
                'Nom',
                'Email',
                'Téléphone',
                'LinkedIn',
                'Offre d\'emploi',
                'Entreprise',
                'Statut',
                'Score IA',
                'Évaluation IA',
                'Date de candidature'
            ];

            foreach ($candidates as $candidate) {
                $analysis = $candidate->cvAnalyses->first();
                $csvData[] = [
                    $candidate->id,
                    $candidate->prenom,
                    $candidate->nom,
                    $candidate->email,
                    $candidate->telephone,
                    $candidate->linkedin_url ?? '',
                    $candidate->jobOffer->title ?? '',
                    $candidate->jobOffer->company_name ?? '',
                    $candidate->status,
                    $analysis->job_match_score ?? '',
                    $analysis->overall_rating ?? '',
                    $candidate->created_at->format('d/m/Y H:i')
                ];
            }

            // Créer le fichier CSV
            $filename = 'candidats_export_' . date('Y-m-d_H-i-s') . '.csv';
            $handle = fopen('php://temp', 'w+');

            foreach ($csvData as $row) {
                fputcsv($handle, $row, ';');
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
