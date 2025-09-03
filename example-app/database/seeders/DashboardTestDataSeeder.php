<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobPosition;
use App\Models\CandidateApplication;
use App\Models\CVAnalysis;
use App\Models\User;
use Carbon\Carbon;

class DashboardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔄 Création de données de test pour le dashboard...\n";

        // 1. Créer des postes
        $jobs = [
            [
                'title' => 'Développeur Full Stack Senior',
                'description' => 'Nous recherchons un développeur expérimenté en Laravel et React pour rejoindre notre équipe technique. Vous travaillerez sur des projets innovants et encadrerez une équipe de développeurs juniors.',
                'required_skills' => 'Laravel, React, PHP, JavaScript, MySQL, Git, 5+ ans d\'expérience en développement web',
                'preferred_qualifications' => 'Docker, AWS, Vue.js, expérience en encadrement d\'équipe',
                'company_info' => 'TechCorp - Entreprise leader dans le développement de solutions web innovantes',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(30),
            ],
            [
                'title' => 'Responsable Marketing Digital',
                'description' => 'Poste de responsable marketing digital pour gérer nos campagnes multi-canaux et développer notre présence en ligne. Vous piloterez la stratégie digitale de l\'entreprise.',
                'required_skills' => 'Marketing digital, SEO, SEM, Google Analytics, Facebook Ads, 3+ ans d\'expérience',
                'preferred_qualifications' => 'Certification Google Ads, expérience e-commerce, maîtrise des outils de marketing automation',
                'company_info' => 'MarketingPro - Agence de marketing digital en pleine croissance',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(20),
            ],
            [
                'title' => 'Analyste RH',
                'description' => 'Rejoignez notre équipe RH en tant qu\'analyste pour optimiser nos processus de recrutement et analyser les données RH. Poste idéal pour débuter dans l\'analyse de données.',
                'required_skills' => 'Excel avancé, Power BI, analyse de données, statistiques, 2+ ans d\'expérience',
                'preferred_qualifications' => 'Python, R, expérience en RH, connaissance des outils SIRH',
                'company_info' => 'HRTech Solutions - Spécialiste des solutions RH digitales',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(10),
            ]
        ];

        foreach ($jobs as $jobData) {
            JobPosition::updateOrCreate(
                ['title' => $jobData['title']], // Critère de recherche
                $jobData // Données à créer/mettre à jour
            );
        }
        echo "✅ " . count($jobs) . " postes créés\n";

        // 2. Créer des utilisateurs candidats
        $candidateUsers = [
            [
                'name' => 'Jean Dupont',
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@email.com',
                'password' => bcrypt('password123'),
                'phone' => '+33123456789',
                'linkedin_url' => 'https://linkedin.com/in/jeandupont',
                'user_type' => 'candidate',
                'role_id' => null, // Les candidats n'ont pas de rôle système
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'name' => 'Marie Martin',
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'email' => 'marie.martin@email.com',
                'password' => bcrypt('password123'),
                'phone' => '+33987654321',
                'linkedin_url' => 'https://linkedin.com/in/mariemartin',
                'user_type' => 'candidate',
                'role_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'name' => 'Pierre Durand',
                'first_name' => 'Pierre',
                'last_name' => 'Durand',
                'email' => 'pierre.durand@email.com',
                'password' => bcrypt('password123'),
                'phone' => '+33456789123',
                'linkedin_url' => 'https://linkedin.com/in/pierredurand',
                'user_type' => 'candidate',
                'role_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'name' => 'Sophie Bernard',
                'first_name' => 'Sophie',
                'last_name' => 'Bernard',
                'email' => 'sophie.bernard@email.com',
                'password' => bcrypt('password123'),
                'phone' => '+33789123456',
                'linkedin_url' => 'https://linkedin.com/in/sophiebernard',
                'user_type' => 'candidate',
                'role_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ]
        ];

        $userIds = [];
        foreach ($candidateUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']], // Critère de recherche
                $userData // Données à créer/mettre à jour
            );
            $userIds[] = $user->id;
        }
        echo "✅ " . count($candidateUsers) . " utilisateurs candidats créés\n";

        // 3. Créer des candidatures
        $candidates = [
            [
                'user_id' => $userIds[0],
                'cv_file_path' => 'cvs/jean_dupont_cv.pdf',
                'cover_letter_path' => 'cover_letters/jean_dupont_cover.pdf',
                'status' => 'pending',
                'ai_analysis' => null,
                'admin_notes' => 'Candidature intéressante pour le poste de développeur',
                'submitted_at' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $userIds[1],
                'cv_file_path' => 'cvs/marie_martin_cv.pdf',
                'cover_letter_path' => 'cover_letters/marie_martin_cover.pdf',
                'status' => 'analyzed',
                'ai_analysis' => null,
                'admin_notes' => 'Profil marketing intéressant',
                'submitted_at' => Carbon::now()->subDays(3),
                'analyzed_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $userIds[2],
                'cv_file_path' => 'cvs/pierre_durand_cv.pdf',
                'cover_letter_path' => 'cover_letters/pierre_durand_cover.pdf',
                'status' => 'pending',
                'ai_analysis' => null,
                'admin_notes' => 'Candidat analyste RH',
                'submitted_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $userIds[3],
                'cv_file_path' => 'cvs/sophie_bernard_cv.pdf',
                'cover_letter_path' => 'cover_letters/sophie_bernard_cover.pdf',
                'status' => 'pending',
                'ai_analysis' => null,
                'admin_notes' => 'Développeuse frontend',
                'submitted_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        ];

        foreach ($candidates as $candidateData) {
            CandidateApplication::updateOrCreate(
                ['user_id' => $candidateData['user_id'], 'cv_file_path' => $candidateData['cv_file_path']], // Critère de recherche
                $candidateData // Données à créer/mettre à jour
            );
        }
        echo "✅ " . count($candidates) . " candidatures créées\n";

        // 3. Créer des analyses CV
        $analyses = [
            [
                'candidate_application_id' => 1,
                'job_position_id' => 1,
                'profile_summary' => 'Développeur Full Stack expérimenté avec solides compétences techniques',
                'key_skills' => ['Laravel', 'React', 'PHP', 'JavaScript', 'MySQL'],
                'education' => [['degree' => 'Master Informatique', 'institution' => 'Université Paris', 'year' => '2018']],
                'experience' => [['position' => 'Développeur Senior', 'company' => 'TechCorp', 'duration' => '3 ans']],
                'languages' => [['language' => 'Français', 'level' => 'Natif'], ['language' => 'Anglais', 'level' => 'Courant']],
                'strengths' => ['Expérience technique solide', 'Capacité d\'adaptation', 'Travail en équipe'],
                'weaknesses' => ['Manque d\'expérience en DevOps', 'Pas de certification cloud'],
                'job_match_score' => 85,
                'job_match_analysis' => 'Excellent candidat correspondant parfaitement au profil recherché',
                'recommendations' => ['Entretien technique approfondi', 'Vérification des références'],
                'overall_rating' => 'A',
                'next_steps' => ['Entretien RH', 'Test technique', 'Entretien final'],
                'analysis_status' => 'completed',
                'analyzed_at' => Carbon::now()->subDays(4),
                'tokens_used' => 1250,
                'cost_estimate' => 0.0025,
            ],
            [
                'candidate_application_id' => 2,
                'job_position_id' => 2,
                'profile_summary' => 'Responsable Marketing Digital avec expertise en campagnes multi-canaux',
                'key_skills' => ['Marketing Digital', 'SEO', 'SEM', 'Google Analytics', 'Facebook Ads'],
                'education' => [['degree' => 'Master Marketing', 'institution' => 'ESSEC', 'year' => '2020']],
                'experience' => [['position' => 'Chef de projet Marketing', 'company' => 'MarketingPro', 'duration' => '2 ans']],
                'languages' => [['language' => 'Français', 'level' => 'Natif'], ['language' => 'Anglais', 'level' => 'Courant']],
                'strengths' => ['Créativité', 'Analyse de données', 'Gestion de projet'],
                'weaknesses' => ['Peu d\'expérience en B2B', 'Connaissance limitée du secteur tech'],
                'job_match_score' => 78,
                'job_match_analysis' => 'Bon candidat avec quelques lacunes à combler',
                'recommendations' => ['Formation sur le secteur tech', 'Entretien avec l\'équipe'],
                'overall_rating' => 'B',
                'next_steps' => ['Entretien RH', 'Présentation de cas pratique'],
                'analysis_status' => 'completed',
                'analyzed_at' => Carbon::now()->subDays(2),
                'tokens_used' => 1100,
                'cost_estimate' => 0.0022,
            ],
            [
                'candidate_application_id' => 3,
                'job_position_id' => 3,
                'analysis_status' => 'pending',
                'created_at' => Carbon::now()->subHours(2),
            ]
        ];

        foreach ($analyses as $analysisData) {
            CVAnalysis::updateOrCreate(
                ['candidate_application_id' => $analysisData['candidate_application_id'] ?? null], // Critère de recherche
                $analysisData // Données à créer/mettre à jour
            );
        }
        echo "✅ " . count($analyses) . " analyses créées\n";

        echo "🎉 Données de test créées avec succès !\n";
        echo "📊 Résumé:\n";
        echo "   - Postes: " . JobPosition::count() . "\n";
        echo "   - Candidatures: " . CandidateApplication::count() . "\n";
        echo "   - Analyses: " . CVAnalysis::count() . "\n";
    }
}
