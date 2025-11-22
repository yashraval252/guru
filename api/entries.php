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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit();
    }
    $title = trim($input['title'] ?? '');
    $date = trim($input['date'] ?? '');

    $errors = [];

    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Title must be 255 characters or fewer.';
    }

    if ($date === '') {
        $errors[] = 'Date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
        $errors[] = 'Date must be a valid date in YYYY-MM-DD format.';
    }

    if ($errors) {
        http_response_code(422);
        echo json_encode(['error' => $errors]);
        exit();
    }

    try {
        $stmt = $db->prepare('INSERT INTO entries (user_id, title, date) VALUES (:user_id, :title, :date)');
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'date' => $date
        ]);
        $entryId = (int)$db->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'entry' => [
                'id' => $entryId,
                'title' => $title,
                'date' => $date
            ]
        ]);
        exit();

    } catch (Exception $e) {
        error_log('Failed to add entry: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add entry.']);
        exit();
    }
} elseif ($method === 'GET') {
    // Optional date filter: ?date=YYYY-MM-DD
    $filterDate = isset($_GET['date']) ? trim((string)$_GET['date']) : '';

    try {
        if ($filterDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
            $stmt = $db->prepare('SELECT id, title, date FROM entries WHERE user_id = :user_id AND date = :date ORDER BY date DESC, id DESC');
            $stmt->execute(['user_id' => $userId, 'date' => $filterDate]);
        } else {
            $stmt = $db->prepare('SELECT id, title, date FROM entries WHERE user_id = :user_id ORDER BY date DESC, id DESC');
            $stmt->execute(['user_id' => $userId]);
        }
        $entries = $stmt->fetchAll();

        echo json_encode(['entries' => $entries]);
        exit();

    } catch (Exception $e) {
        error_log('Failed to fetch entries: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch entries.']);
        exit();
    }

} elseif ($method === 'DELETE') {
    // Delete an entry by id. Accept id as query parameter or JSON body.
    $id = null;
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input) && isset($input['id'])) {
            $id = (int)$input['id'];
        }
    }

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing entry id']);
        exit();
    }

    try {
        // Ensure the entry belongs to the current user
        $check = $db->prepare('SELECT id FROM entries WHERE id = :id AND user_id = :user_id');
        $check->execute(['id' => $id, 'user_id' => $userId]);
        $found = $check->fetch();
        if (!$found) {
            http_response_code(404);
            echo json_encode(['error' => 'Entry not found']);
            exit();
        }

        $del = $db->prepare('DELETE FROM entries WHERE id = :id AND user_id = :user_id');
        $del->execute(['id' => $id, 'user_id' => $userId]);

        echo json_encode(['success' => true, 'id' => $id]);
        exit();

    } catch (Exception $e) {
        error_log('Failed to delete entry: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete entry.']);
        exit();
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}
