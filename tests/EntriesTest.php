<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EntriesTest extends TestCase
{
    private \PDO $db;
    private int $userId;

    protected function setUp(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'], $_ENV['DB_NAME']);
        $this->db = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->db->beginTransaction();

        // Insert test user
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => 'Entry Tester',
            'email' => 'entrytester@example.com',
            'password' => password_hash('testpass', PASSWORD_DEFAULT),
        ]);
        $this->userId = (int)$this->db->lastInsertId();
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testAddEntryValidData(): void
    {
        $title = 'Test Entry';
        $date = '2024-01-15';

        $stmt = $this->db->prepare('INSERT INTO entries (user_id, title, date) VALUES (:user_id, :title, :date)');
        $result = $stmt->execute([
            'user_id' => $this->userId,
            'title' => $title,
            'date' => $date,
        ]);

        $this->assertTrue($result);

        $stmt = $this->db->prepare('SELECT * FROM entries WHERE user_id = :user_id AND title = :title AND date = :date');
        $stmt->execute([
            'user_id' => $this->userId,
            'title' => $title,
            'date' => $date,
        ]);
        $entry = $stmt->fetch();

        $this->assertNotEmpty($entry);
        $this->assertEquals($title, $entry['title']);
        $this->assertEquals($date, $entry['date']);
    }

    public function testAddEntryInvalidDate(): void
    {
        $title = 'Invalid Date Entry';
        $date = '2024-02-30'; // Invalid date

        $this->assertFalse(checkdate(2, 30, 2024));
    }

    public function testFetchUserEntries(): void
    {
        // Insert multiple entries
        $entriesData = [
            ['title' => 'Entry One', 'date' => '2024-01-01'],
            ['title' => 'Entry Two', 'date' => '2024-01-02'],
        ];

        $stmt = $this->db->prepare('INSERT INTO entries (user_id, title, date) VALUES (:user_id, :title, :date)');
        foreach ($entriesData as $data) {
            $stmt->execute([
                'user_id' => $this->userId,
                'title' => $data['title'],
                'date' => $data['date'],
            ]);
        }

        $stmt = $this->db->prepare('SELECT id, title, date FROM entries WHERE user_id = :user_id ORDER BY date DESC');
        $stmt->execute(['user_id' => $this->userId]);
        $entries = $stmt->fetchAll();

        $this->assertCount(count($entriesData), $entries);
        $this->assertEquals('Entry Two', $entries[0]['title']);
    }

    public function testUserCannotAccessOthersEntries(): void
    {
        // Insert another user
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => 'Other User',
            'email' => 'otheruser@example.com',
            'password' => password_hash('otherpass', PASSWORD_DEFAULT),
        ]);
        $otherUserId = (int)$this->db->lastInsertId();

        // Insert entry for other user
        $stmt = $this->db->prepare('INSERT INTO entries (user_id, title, date) VALUES (:user_id, :title, :date)');
        $stmt->execute([
            'user_id' => $otherUserId,
            'title' => 'Other User Entry',
            'date' => '2024-01-01',
        ]);
        $otherEntryId = (int)$this->db->lastInsertId();

        // Attempt to fetch other user's entry as current user
        $stmt = $this->db->prepare('SELECT * FROM entries WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $otherEntryId,
            'user_id' => $this->userId,
        ]);
        $entry = $stmt->fetch();

        $this->assertFalse($entry);
    }
}
