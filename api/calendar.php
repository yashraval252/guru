<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

use App\Database;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$db = Database::getInstance()->getConnection();
$userId = (int)$_SESSION['user_id'];

try {
    $stmt = $db->prepare('SELECT id, title, date FROM entries WHERE user_id = :user_id ORDER BY date DESC');
    $stmt->execute(['user_id' => $userId]);
    $entries = $stmt->fetchAll();

    $events = [];
    foreach ($entries as $entry) {
        $events[] = [
            'id' => (string)$entry['id'],
            'title' => htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8'),
            'date' => $entry['date'],
            'backgroundColor' => '#6366f1',
            'borderColor' => '#4f46e5',
        ];
    }

    http_response_code(200);
    echo json_encode($events);
    exit();

} catch (Exception $e) {
    error_log('Failed to fetch calendar entries: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch entries.']);
    exit();
}
