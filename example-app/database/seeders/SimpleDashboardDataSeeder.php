<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobPosition;
use App\Models\CandidateApplication;
use App\Models\CVAnalysis;
use App\Models\User;
use App\Models\Candidate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SimpleDashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🚀 Création de données de test simples pour le dashboard...\n";

        // 1. Créer des postes de travail
        $this->createJobPositions();

        // 2. Créer des utilisateurs candidats
        $candidateUsers = $this->createCandidateUsers();

        // 3. Créer des candidats dans la table candidates
        $candidates = $this->createCandidates();

        // 4. Créer des candidatures
        $this->createCandidateApplications($candidateUsers);

        // 5. Créer des analyses CV simples
        $this->createCVAnalyses($candidates);

        echo "\n🎉 Données de test créées avec succès !\n";
        $this->displaySummary();
    }

    private function createJobPositions()
    {
        echo "📋 Création des postes...\n";

        $jobs = [
            [
                'title' => 'Développeur Full Stack',
                'description' => 'Nous recherchons un développeur expérimenté pour rejoindre notre équipe technique.',
                'required_skills' => json_encode(['PHP', 'Laravel', 'JavaScript', 'React', 'MySQL']),
                'preferred_qualifications' => json_encode(['Docker', 'AWS', 'Vue.js']),
                'company_info' => 'TechCorp - Entreprise innovante dans le développement web',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Responsable Marketing Digital',
                'description' => 'Poste de responsable marketing pour gérer nos campagnes digitales.',
                'required_skills' => json_encode(['Marketing Digital', 'SEO', 'Google Ads', 'Analytics']),
                'preferred_qualifications' => json_encode(['Certification Google', 'E-commerce']),
                'company_info' => 'MarketingPro - Agence de marketing digital',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Analyste de Données',
                'description' => 'Analyste pour optimiser nos processus et analyser les données.',
                'required_skills' => json_encode(['Excel', 'Power BI', 'SQL', 'Python']),
                'preferred_qualifications' => json_encode(['R', 'Machine Learning']),
                'company_info' => 'DataTech - Solutions d\'analyse de données',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Designer UX/UI',
                'description' => 'Designer pour créer des interfaces utilisateur exceptionnelles.',
                'required_skills' => json_encode(['Figma', 'Adobe XD', 'Photoshop', 'UX Design']),
                'preferred_qualifications' => json_encode(['Prototyping', 'User Research']),
                'company_info' => 'DesignStudio - Agence de design créatif',
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Chef de Projet IT',
                'description' => 'Chef de projet pour coordonner nos développements techniques.',
                'required_skills' => json_encode(['Gestion de projet', 'Agile', 'Scrum', 'JIRA']),
                'preferred_qualifications' => json_encode(['PMP', 'Leadership']),
                'company_info' => 'ProjectTech - Gestion de projets IT',
                'status' => 'draft',
                'created_at' => Carbon::now()->subDays(1),
            ]
        ];

        foreach ($jobs as $jobData) {
            JobPosition::updateOrCreate(
                ['title' => $jobData['title']],
                $jobData
            );
        }

        echo "   ✅ " . count($jobs) . " postes créés\n";
    }

    private function createCandidateUsers()
    {
        echo "👥 Création des utilisateurs candidats...\n";

        $candidates = [
            [
                'name' => 'Jean Dupont',
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33123456789',
                'linkedin_url' => 'https://linkedin.com/in/jeandupont',
                'user_type' => 'candidate',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(8),
            ],
            [
                'name' => 'Marie Martin',
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'email' => 'marie.martin@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33987654321',
                'linkedin_url' => 'https://linkedin.com/in/mariemartin',
                'user_type' => 'candidate',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(6),
            ],
            [
                'name' => 'Pierre Durand',
                'first_name' => 'Pierre',
                'last_name' => 'Durand',
                'email' => 'pierre.durand@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33456789123',
                'linkedin_url' => 'https://linkedin.com/in/pierredurand',
                'user_type' => 'candidate',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'name' => 'Sophie Bernard',
                'first_name' => 'Sophie',
                'last_name' => 'Bernard',
                'email' => 'sophie.bernard@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33789123456',
                'linkedin_url' => 'https://linkedin.com/in/sophiebernard',
                'user_type' => 'candidate',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'name' => 'Lucas Moreau',
                'first_name' => 'Lucas',
                'last_name' => 'Moreau',
                'email' => 'lucas.moreau@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33321654987',
                'linkedin_url' => 'https://linkedin.com/in/lucasmoreau',
                'user_type' => 'candidate',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'name' => 'Emma Leroy',
                'first_name' => 'Emma',
                'last_name' => 'Leroy',
                'email' => 'emma.leroy@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33654321789',
                'linkedin_url' => 'https://linkedin.com/in/emmaleroy',
                'user_type' => 'candidate',
                'is_active' => true,
                'created_at' => Carbon::now(),
            ]
        ];

        $users = [];
        foreach ($candidates as $candidateData) {
            $user = User::updateOrCreate(
                ['email' => $candidateData['email']],
                $candidateData
            );
            $users[] = $user;
        }

        echo "   ✅ " . count($users) . " candidats créés\n";
        return $users;
    }

    private function createCandidates()
    {
        echo "📋 Création des candidats (table candidates)...\n";

        $candidatesData = [
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean.dupont@example.com',
                'telephone' => '+33123456789',
                'linkedin_url' => 'https://linkedin.com/in/jeandupont',
                'cv_path' => 'cvs/jean_dupont_cv.pdf',
                'cover_letter_path' => 'cover_letters/jean_dupont_cover.pdf',
                'status' => 'pending',
                'notes' => 'Excellent profil technique',
                'submitted_at' => Carbon::now()->subDays(7),
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'nom' => 'Martin',
                'prenom' => 'Marie',
                'email' => 'marie.martin@example.com',
                'telephone' => '+33987654321',
                'linkedin_url' => 'https://linkedin.com/in/mariemartin',
                'cv_path' => 'cvs/marie_martin_cv.pdf',
                'cover_letter_path' => 'cover_letters/marie_martin_cover.pdf',
                'status' => 'pending',
                'notes' => 'Profil marketing très intéressant',
                'submitted_at' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'nom' => 'Durand',
                'prenom' => 'Pierre',
                'email' => 'pierre.durand@example.com',
                'telephone' => '+33456789123',
                'linkedin_url' => 'https://linkedin.com/in/pierredurand',
                'cv_path' => 'cvs/pierre_durand_cv.pdf',
                'cover_letter_path' => 'cover_letters/pierre_durand_cover.pdf',
                'status' => 'pending',
                'notes' => 'En attente d\'analyse',
                'submitted_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'nom' => 'Bernard',
                'prenom' => 'Sophie',
                'email' => 'sophie.bernard@example.com',
                'telephone' => '+33789123456',
                'linkedin_url' => 'https://linkedin.com/in/sophiebernard',
                'cv_path' => 'cvs/sophie_bernard_cv.pdf',
                'cover_letter_path' => 'cover_letters/sophie_bernard_cover.pdf',
                'status' => 'pending',
                'notes' => 'Candidature récente',
                'submitted_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
            ]
        ];

        $candidates = [];
        foreach ($candidatesData as $candidateData) {
            $candidate = Candidate::updateOrCreate(
                ['email' => $candidateData['email']],
                $candidateData
            );
            $candidates[] = $candidate;
        }

        echo "   ✅ " . count($candidates) . " candidats (table candidates) créés\n";
        return $candidates;
    }

    private function createCandidateApplications($candidateUsers)
    {
        echo "📄 Création des candidatures...\n";

        $applications = [
            [
                'user_id' => $candidateUsers[0]->id,
                'cv_file_path' => 'cvs/jean_dupont_cv.pdf',
                'cover_letter_path' => 'cover_letters/jean_dupont_cover.pdf',
                'status' => 'analyzed',
                'admin_notes' => 'Excellent profil technique, expérience solide en développement',
                'submitted_at' => Carbon::now()->subDays(7),
                'analyzed_at' => Carbon::now()->subDays(6),
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'user_id' => $candidateUsers[1]->id,
                'cv_file_path' => 'cvs/marie_martin_cv.pdf',
                'cover_letter_path' => 'cover_letters/marie_martin_cover.pdf',
                'status' => 'analyzed',
                'admin_notes' => 'Profil marketing très intéressant, bonne expérience digitale',
                'submitted_at' => Carbon::now()->subDays(5),
                'analyzed_at' => Carbon::now()->subDays(4),
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $candidateUsers[2]->id,
                'cv_file_path' => 'cvs/pierre_durand_cv.pdf',
                'cover_letter_path' => 'cover_letters/pierre_durand_cover.pdf',
                'status' => 'processing',
                'admin_notes' => 'Candidature en cours d\'analyse',
                'submitted_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $candidateUsers[3]->id,
                'cv_file_path' => 'cvs/sophie_bernard_cv.pdf',
                'cover_letter_path' => 'cover_letters/sophie_bernard_cover.pdf',
                'status' => 'pending',
                'admin_notes' => 'Candidature reçue, en attente de traitement',
                'submitted_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $candidateUsers[4]->id,
                'cv_file_path' => 'cvs/lucas_moreau_cv.pdf',
                'cover_letter_path' => 'cover_letters/lucas_moreau_cover.pdf',
                'status' => 'pending',
                'admin_notes' => 'Nouvelle candidature',
                'submitted_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $candidateUsers[5]->id,
                'cv_file_path' => 'cvs/emma_leroy_cv.pdf',
                'cover_letter_path' => 'cover_letters/emma_leroy_cover.pdf',
                'status' => 'pending',
                'admin_notes' => 'Candidature très récente',
                'submitted_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        ];

        foreach ($applications as $appData) {
            CandidateApplication::updateOrCreate(
                ['user_id' => $appData['user_id'], 'cv_file_path' => $appData['cv_file_path']],
                $appData
            );
        }

        echo "   ✅ " . count($applications) . " candidatures créées\n";
    }

    private function createCVAnalyses($candidates)
    {
        echo "🤖 Création des analyses IA...\n";

        $analysesData = [
            [
                'candidate_id' => $candidates[0]->id, // Jean Dupont
                'job_position_id' => 1,
                'profile_summary' => 'Développeur Full Stack expérimenté avec 5 ans d\'expérience',
                'key_skills' => json_encode(['PHP', 'Laravel', 'JavaScript', 'React', 'MySQL', 'Git']),
                'education' => json_encode([['degree' => 'Master Informatique', 'school' => 'Université Paris', 'year' => 2019]]),
                'experience' => json_encode([['title' => 'Développeur Senior', 'company' => 'TechCorp', 'years' => 3]]),
                'job_match_score' => 88,
                'job_match_analysis' => 'Excellent candidat correspondant parfaitement au profil recherché',
                'overall_rating' => 'A',
                'analysis_status' => 'completed',
                'analyzed_at' => Carbon::now()->subDays(6),
                'tokens_used' => 1200,
                'cost_estimate' => 0.024,
                'created_at' => Carbon::now()->subDays(6),
            ],
            [
                'candidate_id' => $candidates[1]->id, // Marie Martin
                'job_position_id' => 2,
                'profile_summary' => 'Responsable Marketing Digital avec expertise en campagnes multi-canaux',
                'key_skills' => json_encode(['Marketing Digital', 'SEO', 'Google Ads', 'Analytics', 'Social Media']),
                'education' => json_encode([['degree' => 'Master Marketing', 'school' => 'ESSEC', 'year' => 2020]]),
                'experience' => json_encode([['title' => 'Chef de projet Marketing', 'company' => 'MarketingPro', 'years' => 2]]),
                'job_match_score' => 82,
                'job_match_analysis' => 'Très bon candidat avec une solide expérience marketing',
                'overall_rating' => 'B',
                'analysis_status' => 'completed',
                'analyzed_at' => Carbon::now()->subDays(4),
                'tokens_used' => 1100,
                'cost_estimate' => 0.022,
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'candidate_id' => $candidates[2]->id, // Pierre Durand
                'job_position_id' => 3,
                'analysis_status' => 'pending',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'candidate_id' => $candidates[3]->id, // Sophie Bernard
                'job_position_id' => 4,
                'analysis_status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
            ]
        ];

        foreach ($analysesData as $analysisData) {
            CVAnalysis::updateOrCreate(
                ['candidate_id' => $analysisData['candidate_id'], 'job_position_id' => $analysisData['job_position_id']],
                $analysisData
            );
        }

        echo "   ✅ " . count($analysesData) . " analyses IA créées\n";
    }

    private function displaySummary()
    {
        echo "\n📊 RÉSUMÉ DES DONNÉES CRÉÉES:\n";
        echo "=" . str_repeat("=", 40) . "\n";
        echo "📋 Postes: " . JobPosition::count() . "\n";
        echo "👥 Utilisateurs candidats: " . User::where('user_type', 'candidate')->count() . "\n";
        echo "📋 Candidats (table candidates): " . Candidate::count() . "\n";
        echo "📄 Candidatures: " . CandidateApplication::count() . "\n";
        echo "🤖 Analyses: " . CVAnalysis::count() . "\n";
        echo "\n📈 STATISTIQUES:\n";
        echo "   - Candidatures en attente: " . CandidateApplication::where('status', 'pending')->count() . "\n";
        echo "   - Candidatures en traitement: " . CandidateApplication::where('status', 'processing')->count() . "\n";
        echo "   - Candidatures analysées: " . CandidateApplication::where('status', 'analyzed')->count() . "\n";
        echo "   - Analyses terminées: " . CVAnalysis::where('analysis_status', 'completed')->count() . "\n";
        echo "   - Analyses en attente: " . CVAnalysis::where('analysis_status', 'pending')->count() . "\n";
        echo "   - Postes actifs: " . JobPosition::where('status', 'active')->count() . "\n";
        echo "\n🎯 Dashboard prêt avec des données réalistes !\n";
    }
}
