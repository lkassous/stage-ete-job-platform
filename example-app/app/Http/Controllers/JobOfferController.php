<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class JobOfferController extends Controller
{
    /**
     * Display a listing of active job offers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si c'est une requête admin (avec token), montrer toutes les offres
            $isAdminRequest = $request->header('Authorization');

            if ($isAdminRequest) {
                // Pour les admins : toutes les offres avec le nombre de candidatures
                $query = JobOffer::withCount('candidates');
            } else {
                // Pour le public : utiliser le cache Redis (5 minutes)
                $cacheKey = 'public_job_offers';

                $offers = Cache::remember($cacheKey, 300, function () {
                    return JobOffer::select(['id', 'title', 'type', 'description', 'requirements', 'location', 'contract_type', 'salary_range', 'company_name', 'experience_level', 'application_deadline', 'status', 'positions_available', 'created_at'])
                        ->where('status', 'active')
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->get();
                });

                return response()->json([
                    'success' => true,
                    'data' => $offers,
                    'total' => $offers->count(),
                    'cached' => true
                ]);
            }

            // Pour les requêtes admin uniquement
            // Filter by type if provided
            if ($request->has('type') && in_array($request->type, ['emploi', 'stage'])) {
                $query->byType($request->type);
            }

            // Filter by location if provided
            if ($request->has('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            // Filter by experience level if provided
            if ($request->has('experience_level')) {
                $query->where('experience_level', $request->experience_level);
            }

            // Filter by status for admin requests
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Optimisation : limiter à 20 offres max et utiliser un index
            $offers = $query->orderBy('created_at', 'desc')->limit(20)->get();

            return response()->json([
                'success' => true,
                'data' => $offers,
                'total' => $offers->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des offres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint ultra-rapide pour le frontend Angular
     */
    public function getFastPublicOffers(): JsonResponse
    {
        try {
            // Cache Redis ultra-rapide (10 minutes)
            $cacheKey = 'fast_public_offers';

            $offers = Cache::remember($cacheKey, 600, function () {
                return JobOffer::select(['id', 'title', 'type', 'description', 'location', 'contract_type', 'company_name', 'experience_level'])
                    ->where('status', 'active')
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get();
            });

            return response()->json([
                'success' => true,
                'data' => $offers,
                'total' => $offers->count(),
                'cached' => true,
                'fast' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des offres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint de test ultra-simple (sans base de données)
     */
    public function testSpeed(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['id' => 1, 'title' => 'Test Offer 1', 'type' => 'emploi'],
                ['id' => 2, 'title' => 'Test Offer 2', 'type' => 'stage'],
                ['id' => 3, 'title' => 'Test Offer 3', 'type' => 'emploi']
            ],
            'total' => 3,
            'test' => true,
            'timestamp' => now()
        ]);
    }

    /**
     * Store a newly created job offer.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:emploi,stage',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'location' => 'required|string|max:255',
            'contract_type' => 'required|in:CDI,CDD,Stage,Freelance,Alternance',
            'company_name' => 'required|string|max:255',
            'experience_level' => 'required|in:junior,intermediate,senior,expert',
            'salary_range' => 'nullable|string|max:255',
            'company_description' => 'nullable|string',
            'skills_required' => 'nullable|array',
            'application_deadline' => 'nullable|date|after:today',
            'positions_available' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jobOffer = JobOffer::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Offre créée avec succès',
                'data' => $jobOffer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'offre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified job offer.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $jobOffer = JobOffer::with('candidates')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $jobOffer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Offre non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified job offer.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:emploi,stage',
            'description' => 'sometimes|required|string',
            'requirements' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'contract_type' => 'sometimes|required|in:CDI,CDD,Stage,Freelance,Alternance',
            'company_name' => 'sometimes|required|string|max:255',
            'experience_level' => 'sometimes|required|in:junior,intermediate,senior,expert',
            'status' => 'sometimes|required|in:active,inactive,closed',
            'salary_range' => 'nullable|string|max:255',
            'company_description' => 'nullable|string',
            'skills_required' => 'nullable|array',
            'application_deadline' => 'nullable|date',
            'positions_available' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jobOffer = JobOffer::findOrFail($id);
            $jobOffer->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Offre mise à jour avec succès',
                'data' => $jobOffer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'offre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified job offer.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $jobOffer = JobOffer::findOrFail($id);
            $jobOffer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Offre supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'offre',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
