<?php
// Test simple d'envoi d'email
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs HTML
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/../vendor/autoload.php';
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur autoload: ' . $e->getMessage()]);
    exit;
}

use Illuminate\Support\Facades\Password;

try {
    // Bootstrap Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    header('Content-Type: application/json');
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de bootstrap: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email requis']);
        exit;
    }
    
    try {
        $status = Password::sendResetLink(['email' => $email]);
        
        if ($status === Password::RESET_LINK_SENT) {
            echo json_encode([
                'success' => true,
                'message' => 'Email envoyé avec succès !',
                'status' => $status
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur: ' . $status,
                'status' => $status
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
