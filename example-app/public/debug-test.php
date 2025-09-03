<?php
header('Content-Type: application/json');

// Test 4: Vérifier kernel
try {
    require_once __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo json_encode(['step' => 4, 'message' => 'Kernel OK']);
    exit;
} catch (Exception $e) {
    echo json_encode(['error' => 'Kernel error: ' . $e->getMessage()]);
    exit;
}
?>
