<?php
/**
 * Models API Endpoint - List available models
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Initialize database if needed
if (!file_exists(DB_PATH)) {
    Database::init();
}

$db = Database::getInstance();

// Get all enabled models
$result = $db->query("SELECT * FROM models WHERE enabled = 1 ORDER BY intelligence_rank ASC");

$models = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $models[] = [
        'id' => (int)$row['id'],
        'platform' => $row['platform'],
        'model_id' => $row['model_id'],
        'display_name' => $row['display_name'],
        'intelligence_rank' => (int)$row['intelligence_rank'],
        'speed_rank' => (int)$row['speed_rank'],
        'size_label' => $row['size_label'],
        'rpm_limit' => $row['rpm_limit'] ? (int)$row['rpm_limit'] : null,
        'rpd_limit' => $row['rpd_limit'] ? (int)$row['rpd_limit'] : null,
        'tpm_limit' => $row['tpm_limit'] ? (int)$row['tpm_limit'] : null,
        'tpd_limit' => $row['tpd_limit'] ? (int)$row['tpd_limit'] : null,
        'monthly_token_budget' => $row['monthly_token_budget'],
        'context_window' => (int)$row['context_window'],
        'enabled' => (bool)$row['enabled'],
    ];
}

echo json_encode($models);
