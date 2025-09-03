<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIService
{
    private ?string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        $this->maxTokens = config('services.openai.max_tokens', 2000);
        $this->temperature = config('services.openai.temperature', 0.3);

        // Note: On ne lance pas d'exception ici pour permettre les tests de validation
    }

    /**
     * Analyser un CV avec ChatGPT
     */
    public function analyzeCv(string $cvText, string $coverLetterText = '', string $jobDescription = ''): array
    {
        try {
            if (empty($this->apiKey)) {
                throw new Exception('OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file.');
            }

            $prompt = $this->buildCvAnalysisPrompt($cvText, $coverLetterText, $jobDescription);
            
            $response = $this->makeRequest([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object']
            ]);

            if (!$response['success']) {
                throw new Exception($response['error']);
            }

            $analysis = json_decode($response['data']['choices'][0]['message']['content'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response from OpenAI');
            }

            return [
                'success' => true,
                'analysis' => $analysis,
                'usage' => $response['data']['usage'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('OpenAI CV Analysis Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'analysis' => null
            ];
        }
    }

    /**
     * Construire le prompt pour l'analyse CV
     */
    private function buildCvAnalysisPrompt(string $cvText, string $coverLetterText, string $jobDescription): string
    {
        $prompt = "Analysez le CV suivant et fournissez une analyse structurée :\n\n";
        
        $prompt .= "=== CV ===\n" . $cvText . "\n\n";
        
        if (!empty($coverLetterText)) {
            $prompt .= "=== LETTRE DE MOTIVATION ===\n" . $coverLetterText . "\n\n";
        }
        
        if (!empty($jobDescription)) {
            $prompt .= "=== DESCRIPTION DU POSTE ===\n" . $jobDescription . "\n\n";
        }
        
        $prompt .= "Veuillez analyser ce candidat et fournir une réponse JSON avec la structure suivante :\n";
        $prompt .= "{\n";
        $prompt .= '  "profile_summary": "Résumé professionnel du candidat en 2-3 phrases",';
        $prompt .= '  "key_skills": ["compétence1", "compétence2", "compétence3"],';
        $prompt .= '  "education": [{"degree": "diplôme", "institution": "établissement", "year": "année"}],';
        $prompt .= '  "experience": [{"position": "poste", "company": "entreprise", "duration": "durée", "description": "description"}],';
        $prompt .= '  "languages": [{"language": "langue", "level": "niveau"}],';
        $prompt .= '  "strengths": ["force1", "force2", "force3"],';
        $prompt .= '  "weaknesses": ["faiblesse1", "faiblesse2"],';
        $prompt .= '  "job_match_score": 85,';
        $prompt .= '  "job_match_analysis": "Analyse de l\'adéquation avec le poste",';
        $prompt .= '  "recommendations": ["recommandation1", "recommandation2"],';
        $prompt .= '  "overall_rating": "A",';
        $prompt .= '  "next_steps": ["étape1", "étape2"]';
        $prompt .= "}\n\n";
        
        return $prompt;
    }

    /**
     * Prompt système pour définir le rôle de l'IA
     */
    private function getSystemPrompt(): string
    {
        return "Vous êtes un expert en recrutement et analyse de CV. Votre rôle est d'analyser les CV et lettres de motivation pour aider les recruteurs à évaluer les candidats. 

Vous devez :
1. Extraire les informations clés du CV de manière structurée
2. Évaluer les compétences et l'expérience du candidat
3. Analyser l'adéquation avec le poste si une description est fournie
4. Fournir des recommandations constructives
5. Donner une note globale (A, B, C, D, E)
6. Suggérer les prochaines étapes du processus de recrutement

Soyez objectif, professionnel et constructif dans vos analyses. Répondez toujours en JSON valide avec la structure demandée.";
    }

    /**
     * Faire une requête à l'API OpenAI
     */
    private function makeRequest(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl . '/chat/completions', $data);

            if (!$response->successful()) {
                $error = $response->json()['error']['message'] ?? 'Unknown OpenAI API error';
                throw new Exception("OpenAI API Error: " . $error);
            }

            return [
                'success' => true,
                'data' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extraire le texte d'un fichier PDF (placeholder)
     */
    public function extractTextFromPdf(string $filePath): string
    {
        // TODO: Implémenter l'extraction de texte PDF
        // Pour l'instant, retourner un texte d'exemple
        return "Texte extrait du PDF : " . basename($filePath);
    }

    /**
     * Valider la configuration OpenAI
     */
    public function validateConfiguration(): array
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'Clé API OpenAI non configurée. Veuillez définir OPENAI_API_KEY dans votre fichier .env'
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(10)->get($this->baseUrl . '/models');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Configuration OpenAI valide'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Clé API OpenAI invalide'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur de connexion à OpenAI: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques d'utilisation
     */
    public function getUsageStats(): array
    {
        // TODO: Implémenter le suivi des statistiques d'utilisation
        return [
            'total_requests' => 0,
            'total_tokens' => 0,
            'cost_estimate' => 0.00
        ];
    }
}
