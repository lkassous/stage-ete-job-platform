<?php
// Script simple et robuste pour l'envoi d'emails de réinitialisation
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Charger Laravel
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Récupérer les données POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email requis']);
        exit;
    }
    
    $email = $input['email'];
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
        exit;
    }
    
    // Envoyer l'email de réinitialisation
    $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);
    
    if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
        echo json_encode([
            'success' => true,
            'message' => 'Email de réinitialisation envoyé avec succès !',
            'status' => $status
        ]);
    } else {
        // Gérer les différents statuts d'erreur
        $message = 'Erreur lors de l\'envoi de l\'email';
        
        switch ($status) {
            case \Illuminate\Support\Facades\Password::INVALID_USER:
                $message = 'Aucun utilisateur trouvé avec cette adresse email';
                break;
            case \Illuminate\Support\Facades\Password::RESET_THROTTLED:
                $message = 'Trop de tentatives. Veuillez attendre avant de réessayer';
                break;
        }
        
        echo json_encode([
            'success' => false,
            'message' => $message,
            'status' => $status
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>
