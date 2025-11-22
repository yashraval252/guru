<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !isset($input['text']) || !is_string($input['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$text = trim($input['text']);
if ($text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty text input']);
    exit();
}

// For demonstration, use a simple regex-based extraction for date and title.
// In production, integrate with an actual open-source NLU model or local service.

// Extract date in YYYY-MM-DD from text (simple ISO date or common date formats)
function extractDate(string $text): ?string {
    // Try to find YYYY-MM-DD format first
    if (preg_match('/\b(\d{4}-\d{2}-\d{2})\b/', $text, $matches)) {
        $date = $matches[1];
        if (strtotime($date) !== false) {
            return $date;
        }
    }

    // Try to find dd/mm/yyyy or dd-mm-yyyy format
    if (preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})\b/', $text, $matches)) {
        $date = str_replace(['/', '-'], '-', $matches[1]);
        $parts = explode('-', $date);
        if (count($parts) === 3) {
            [$day, $month, $year] = $parts;
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);
            }
        }
    }

    // Try to find month name and day, year (e.g., March 10 2024)
    if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2}),?\s+(\d{4})\b/i', $text, $matches)) {
        $month = date_parse($matches[1])['month'];
        $day = (int)$matches[2];
        $year = (int)$matches[3];
        if ($month && checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }

    return null;
}

// Extract title from text by removing date part and common fillers
function extractTitle(string $text, ?string $date): string {
    $title = $text;

    if ($date !== null) {
        $title = str_replace($date, '', $title);
    }
    // Remove common phrases
    $patterns = [
        '/har mahadev/i',
        '/add entry/i',
        '/on/i',
        '/for/i',
        '/please/i',
        '/my/i',
        '/date/i',
        '/title/i',
        '/^,|,$/',
    ];
    $title = preg_replace($patterns, '', $title);
    $title = trim($title);
    // Limit title length
    if (mb_strlen($title) > 255) {
        $title = mb_substr($title, 0, 255);
    }
    return $title !== '' ? $title : 'Untitled Entry';
}

$date = extractDate($text);
$title = extractTitle($text, $date);

if ($title === '' && $date === null) {
    http_response_code(422);
    echo json_encode(['error' => 'Could not extract title or date from input']);
    exit();
}

echo json_encode([
    'title' => $title,
    'date' => $date ?? ''
]);
exit();
