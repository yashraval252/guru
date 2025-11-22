<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'], $_SESSION['user_name'])) {
    header('Location: index.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userName = (string)$_SESSION['user_name'];

require_once __DIR__ . '/classes/Database.php';

use App\Database;

$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->prepare('SELECT id, title, date FROM entries WHERE user_id = :user_id ORDER BY date DESC, id DESC LIMIT 10');
    $stmt->execute(['user_id' => $userId]);
    $recentEntries = $stmt->fetchAll();
} catch (Exception $e) {
    $recentEntries = [];
    error_log('Failed to fetch entries: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Har Mahadev Voice Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/styles.css" />
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">🙏 Har Mahadev</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto">
                    <span class="navbar-text me-3">Welcome, <strong><?=htmlspecialchars($userName, ENT_QUOTES, 'UTF-8')?></strong></span>
                    <a href="auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="section-title">Dashboard</h1>
            </div>
        </div>

        <div class="row g-4">
            <!-- Calendar Section -->
            <div class="col-lg-8">
                <div class="calendar-wrapper">
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Sidebar Section -->
            <div class="col-lg-4">
                <!-- Add Entry Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Entry</h5>
                    </div>
                    <div class="card-body">
                        <form id="entryForm" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required maxlength="255" placeholder="Enter entry title" autocomplete="off" />
                                <div class="input-error" id="titleError" aria-live="polite"></div>
                            </div>

                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required />
                                <div class="input-error" id="dateError" aria-live="polite"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">Add Entry</button>
                        </form>

                        <div class="mt-3">
                            <button id="voiceCommandButton" class="btn btn-voice w-100 py-2" aria-label="Start voice command">
                                🎤 Voice Command
                            </button>
                        </div>
                        <div id="voiceStatus" class="voice-status mt-2" aria-live="polite"></div>
                    </div>
                </div>

                <!-- Recent Entries Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Entries</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEntries)): ?>
                            <p class="text-muted mb-0">No entries found. Start by creating your first entry!</p>
                        <?php else: ?>
                            <ul class="entries-list" id="entriesList" aria-live="polite">
                                <?php foreach ($recentEntries as $entry): ?>
                                    <li class="entry-item">
                                        <div>
                                            <p class="entry-title"><?=htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8')?></p>
                                            <time class="entry-date" datetime="<?=htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8')?>">
                                                <?=date('M d, Y', strtotime($entry['date']))?>
                                            </time>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.USER_ID = <?=json_encode($userId, JSON_THROW_ON_ERROR)?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="js/scripts.js" defer></script>
    <script type="module" src="js/voiceCommand.js" defer></script>
    <script src="js/calendar.js" defer></script>
</body>
</html>
