<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

use GuzzleHttp\Client;

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

if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No audio file uploaded or upload error']);
    exit();
}

$audioTmpPath = $_FILES['audio']['tmp_name'];
$audioMimeType = $_FILES['audio']['type'];

$allowedMimeTypes = ['audio/wav', 'audio/webm', 'audio/ogg', 'audio/mp3', 'audio/mpeg', 'audio/x-wav', 'audio/x-mpeg-3'];
if (!in_array($audioMimeType, $allowedMimeTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported audio file type']);
    exit();
}

$openAiApiKey = $_ENV['OPENAI_API_KEY'] ?? '';
if ($openAiApiKey === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Speech-to-text API key not configured']);
    exit();
}

try {
    $client = new Client([
        'base_uri' => 'https://api.openai.com/v1/',
        'timeout' => 30,
    ]);

    $fileData = fopen($audioTmpPath, 'r');

    $response = $client->request('POST', 'audio/transcriptions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $openAiApiKey,
        ],
        'multipart' => [
            [
                'name' => 'file',
                'contents' => $fileData,
                'filename' => $_FILES['audio']['name'],
            ],
            [
                'name' => 'model',
                'contents' => 'whisper-1',
            ],
            [
                'name' => 'language',
                'contents' => 'hi,en,gu',
            ],
        ],
    ]);

    $responseData = json_decode((string)$response->getBody(), true);
    if (!isset($responseData['text'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to transcribe audio']);
        exit();
    }

    echo json_encode(['text' => $responseData['text']]);
    exit();

} catch (Exception $e) {
    error_log('Voice process error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to process audio']);
    exit();
}
