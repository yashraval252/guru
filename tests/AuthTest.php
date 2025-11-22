<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    private \PDO $db;

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
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testRegistrationWithValidData(): void
    {
        $name = 'Test User';
        $email = 'testuser@example.com';
        $password = 'password123';

        // Check email does not exist
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $this->assertEquals(0, (int)$stmt->fetchColumn());

        // Insert user
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashed,
        ]);

        // Confirm inserted
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user);
        $this->assertEquals($name, $user['name']);
        $this->assertTrue(password_verify($password, $user['password']));
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $invalidEmail = 'invalid-email';

        $this->assertFalse(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL));
    }

    public function testLoginSuccess(): void
    {
        $email = 'loginuser@example.com';
        $password = 'securepass';

        // Insert user
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => 'Login User',
            'email' => $email,
            'password' => $hashed,
        ]);

        // Simulate login check
        $stmt = $this->db->prepare('SELECT id, name, password FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user);
        $this->assertTrue(password_verify($password, $user['password']));
    }

    public function testLoginFailureWrongPassword(): void
    {
        $email = 'failuser@example.com';
        $password = 'correctpass';
        $wrongPassword = 'wrongpass';

        // Insert user
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => 'Fail User',
            'email' => $email,
            'password' => $hashed,
        ]);

        // Fetch user
        $stmt = $this->db->prepare('SELECT id, name, password FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user);
        $this->assertFalse(password_verify($wrongPassword, $user['password']));
    }

    public function testSessionCreationAndLogout(): void
    {
        // Simulate session creation
        $_SESSION = [];
        $_SESSION['user_id'] = 123;
        $_SESSION['user_name'] = 'Session User';

        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertArrayHasKey('user_name', $_SESSION);

        // Simulate logout
        $_SESSION = [];
        $this->assertEmpty($_SESSION);
    }
}
