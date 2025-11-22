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
    $stmt = $db->prepare('SELECT id, title, date FROM entries WHERE user_id = :user_id ORDER BY date DESC, id DESC');
    $stmt->execute(['user_id' => $userId]);
    $entries = $stmt->fetchAll();
} catch (Exception $e) {
    $entries = [];
    error_log('Failed to fetch entries: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Har Mahadev Voice Entry</title>
    <link rel="stylesheet" href="styles/styles.css" />
</head>
<body>
    <header class="container">
        <h1>Welcome, <?=htmlspecialchars($userName, ENT_QUOTES, 'UTF-8')?></h1>
        <nav>
            <a href="auth/logout.php" class="logout-button" aria-label="Logout">Logout</a>
        </nav>
    </header>
    <main class="container">
        <section aria-labelledby="addEntryHeading">
            <h2 id="addEntryHeading">Add New Entry</h2>
            <form id="entryForm" method="POST" novalidate>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required maxlength="255" autocomplete="off" />
                <div class="input-error" id="titleError" aria-live="polite"></div>

                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required />
                <div class="input-error" id="dateError" aria-live="polite"></div>

                <button type="submit">Add Entry</button>
            </form>
            <button id="voiceCommandButton" aria-label="Start voice command">🎤 Voice Command</button>
            <div id="voiceStatus" aria-live="polite"></div>
        </section>
        <section aria-labelledby="entriesHeading">
            <h2 id="entriesHeading">Your Entries</h2>
            <ul id="entriesList" aria-live="polite">
                <?php if (empty($entries)): ?>
                    <li>No entries found.</li>
                <?php else: ?>
                    <?php foreach ($entries as $entry): ?>
                        <li>
                            <strong><?=htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8')?></strong>
                            <time datetime="<?=htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8')?></time>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
    </main>
    <script>
        window.USER_ID = <?=json_encode($userId, JSON_THROW_ON_ERROR)?>;
    </script>
    <script src="js/scripts.js" defer></script>
    <script type="module" src="js/voiceCommand.js" defer></script>
</body>
</html>
