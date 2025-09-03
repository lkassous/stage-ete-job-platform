<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧠 Test de l'intégration OpenAI pour l'analyse CV\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // 1. Test de la configuration OpenAI
    echo "📋 1. Validation de la configuration OpenAI...\n";
    
    $openAIService = app(\App\Services\OpenAIService::class);
    $configResult = $openAIService->validateConfiguration();
    
    if ($configResult['success']) {
        echo "  ✅ Configuration OpenAI valide\n";
    } else {
        echo "  ❌ Configuration OpenAI invalide: " . $configResult['error'] . "\n";
        echo "  💡 Vérifiez votre clé API dans le fichier .env (OPENAI_API_KEY)\n\n";
        
        // Continuer avec un test simulé
        echo "📝 2. Test avec données simulées...\n";
        testWithMockData();
        exit;
    }

    // 2. Test d'analyse CV avec du texte réel
    echo "\n📝 2. Test d'analyse CV avec du texte d'exemple...\n";
    
    $cvText = "
    JEAN DUPONT
    Développeur Full Stack Senior
    Email: jean.dupont@email.com
    Téléphone: +33 1 23 45 67 89
    
    EXPÉRIENCE PROFESSIONNELLE:
    
    Développeur Full Stack Senior - TechCorp (2020-2024)
    • Développement d'applications web avec Laravel et React
    • Gestion d'équipe de 5 développeurs
    • Architecture microservices avec Docker
    • Amélioration des performances de 40%
    
    Développeur Web - WebAgency (2018-2020)
    • Création de sites e-commerce avec Symfony
    • Intégration d'APIs REST
    • Optimisation SEO
    
    FORMATION:
    • Master Informatique - Université Paris (2018)
    • Licence Informatique - Université Lyon (2016)
    
    COMPÉTENCES:
    • Langages: PHP, JavaScript, Python, SQL
    • Frameworks: Laravel, Symfony, React, Vue.js
    • Bases de données: MySQL, PostgreSQL, MongoDB
    • DevOps: Docker, Git, CI/CD
    • Langues: Français (natif), Anglais (courant)
    ";
    
    $coverLetter = "
    Madame, Monsieur,
    
    Passionné par le développement web depuis plus de 6 ans, je souhaite rejoindre votre équipe 
    en tant que développeur senior. Mon expérience en architecture logicielle et ma capacité à 
    encadrer des équipes techniques seraient des atouts pour vos projets.
    
    Cordialement,
    Jean Dupont
    ";
    
    $jobDescription = "
    Nous recherchons un Développeur Full Stack Senior pour rejoindre notre équipe.
    
    Missions:
    • Développer des applications web modernes
    • Encadrer une équipe de développeurs
    • Participer à l'architecture technique
    
    Compétences requises:
    • 5+ ans d'expérience en développement web
    • Maîtrise de PHP/Laravel et JavaScript
    • Expérience en gestion d'équipe
    • Connaissance Docker/DevOps
    ";
    
    echo "  🔄 Analyse en cours avec OpenAI...\n";
    
    $result = $openAIService->analyzeCv($cvText, $coverLetter, $jobDescription);
    
    if ($result['success']) {
        echo "  ✅ Analyse terminée avec succès!\n\n";
        
        $analysis = $result['analysis'];
        
        echo "📊 RÉSULTATS DE L'ANALYSE:\n";
        echo "=" . str_repeat("=", 30) . "\n";
        
        echo "👤 Résumé professionnel:\n";
        echo "   " . ($analysis['profile_summary'] ?? 'Non disponible') . "\n\n";
        
        echo "🎯 Score d'adéquation: " . ($analysis['job_match_score'] ?? 'N/A') . "/100\n";
        echo "📝 Note globale: " . ($analysis['overall_rating'] ?? 'N/A') . "\n\n";
        
        if (!empty($analysis['key_skills'])) {
            echo "💪 Compétences clés:\n";
            foreach ($analysis['key_skills'] as $skill) {
                echo "   • $skill\n";
            }
            echo "\n";
        }
        
        if (!empty($analysis['strengths'])) {
            echo "✅ Points forts:\n";
            foreach ($analysis['strengths'] as $strength) {
                echo "   • $strength\n";
            }
            echo "\n";
        }
        
        if (!empty($analysis['weaknesses'])) {
            echo "⚠️ Points d'amélioration:\n";
            foreach ($analysis['weaknesses'] as $weakness) {
                echo "   • $weakness\n";
            }
            echo "\n";
        }
        
        if (!empty($analysis['recommendations'])) {
            echo "💡 Recommandations:\n";
            foreach ($analysis['recommendations'] as $recommendation) {
                echo "   • $recommendation\n";
            }
            echo "\n";
        }
        
        if (!empty($analysis['next_steps'])) {
            echo "🚀 Prochaines étapes:\n";
            foreach ($analysis['next_steps'] as $step) {
                echo "   • $step\n";
            }
            echo "\n";
        }
        
        // Afficher les statistiques d'utilisation
        if (isset($result['usage'])) {
            $usage = $result['usage'];
            echo "📈 STATISTIQUES D'UTILISATION:\n";
            echo "   Tokens utilisés: " . ($usage['total_tokens'] ?? 'N/A') . "\n";
            echo "   Tokens d'entrée: " . ($usage['prompt_tokens'] ?? 'N/A') . "\n";
            echo "   Tokens de sortie: " . ($usage['completion_tokens'] ?? 'N/A') . "\n";
            
            // Calcul du coût estimé
            $totalTokens = $usage['total_tokens'] ?? 0;
            if ($totalTokens > 0) {
                $inputTokens = $totalTokens * 0.7;
                $outputTokens = $totalTokens * 0.3;
                $inputCost = ($inputTokens / 1000) * 0.00015;
                $outputCost = ($outputTokens / 1000) * 0.0006;
                $totalCost = $inputCost + $outputCost;
                echo "   Coût estimé: $" . number_format($totalCost, 4) . "\n";
            }
            echo "\n";
        }
        
    } else {
        echo "  ❌ Erreur lors de l'analyse: " . $result['error'] . "\n\n";
    }

    // 3. Test de création d'une analyse en base de données
    echo "💾 3. Test de création d'analyse en base de données...\n";
    
    // Vérifier s'il y a des candidatures existantes
    $candidateApp = \App\Models\CandidateApplication::first();
    $jobPosition = \App\Models\JobPosition::first();
    
    if ($candidateApp && $jobPosition) {
        echo "  📋 Candidature trouvée: " . $candidateApp->first_name . " " . $candidateApp->last_name . "\n";
        echo "  💼 Poste trouvé: " . $jobPosition->title . "\n";
        
        // Créer une analyse CV
        $cvAnalysis = \App\Models\CVAnalysis::create([
            'candidate_application_id' => $candidateApp->id,
            'job_position_id' => $jobPosition->id,
            'analysis_status' => 'pending',
        ]);
        
        echo "  ✅ Analyse CV créée avec l'ID: " . $cvAnalysis->id . "\n";
        
        // Simuler le processus d'analyse
        if ($result['success']) {
            $cvAnalysis->markAsCompleted($result['analysis'], $result['usage'] ?? []);
            echo "  ✅ Analyse marquée comme terminée\n";
        }
        
    } else {
        echo "  ⚠️ Aucune candidature ou poste trouvé en base de données\n";
        echo "  💡 Créez d'abord des candidatures via l'interface web\n";
    }

    echo "\n🎉 Test d'intégration OpenAI terminé avec succès!\n";
    echo "\n💡 Prochaines étapes:\n";
    echo "   1. Configurez votre vraie clé API OpenAI dans .env\n";
    echo "   2. Testez l'API via les routes HTTP\n";
    echo "   3. Intégrez avec le frontend Angular\n";
    echo "   4. Ajoutez l'extraction de texte PDF\n";

} catch (\Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

function testWithMockData() {
    echo "  🔄 Simulation d'analyse avec données fictives...\n";
    
    $mockAnalysis = [
        'profile_summary' => 'Développeur Full Stack expérimenté avec 6 ans d\'expérience en développement web moderne.',
        'key_skills' => ['PHP', 'Laravel', 'JavaScript', 'React', 'Docker', 'Leadership'],
        'education' => [
            ['degree' => 'Master Informatique', 'institution' => 'Université Paris', 'year' => '2018']
        ],
        'experience' => [
            ['position' => 'Développeur Full Stack Senior', 'company' => 'TechCorp', 'duration' => '4 ans']
        ],
        'languages' => [
            ['language' => 'Français', 'level' => 'Natif'],
            ['language' => 'Anglais', 'level' => 'Courant']
        ],
        'strengths' => ['Expérience technique solide', 'Capacité de leadership', 'Polyvalence technologique'],
        'weaknesses' => ['Manque d\'expérience en IA', 'Pas de certification cloud'],
        'job_match_score' => 85,
        'job_match_analysis' => 'Excellent candidat avec toutes les compétences requises.',
        'recommendations' => ['Entretien technique approfondi', 'Vérification des références'],
        'overall_rating' => 'A',
        'next_steps' => ['Entretien RH', 'Test technique', 'Entretien final']
    ];
    
    echo "  ✅ Analyse simulée générée\n";
    echo "  📊 Score d'adéquation: " . $mockAnalysis['job_match_score'] . "/100\n";
    echo "  📝 Note globale: " . $mockAnalysis['overall_rating'] . "\n";
}
