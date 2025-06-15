<?php
// includes/auth.php
// Kullanıcı kimlik doğrulama sınıfı
require_once __DIR__ . '/db.php';

class Auth
{
    private static function db(): PDO
    {
        return (new Database())->getConn();
    }

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Yeni kullanıcı kayıt. Başarılı olduğunda true, başarısız olduğunda hata mesajı döndürür.
     */
    public static function register(string $name, string $email, string $password, string $phone = '', string $city = '', string $gender = '')
    {
        self::startSession();
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Geçersiz e-posta adresi';
        }
        if (strlen($password) < 6) {
            return 'Şifre en az 6 karakter olmalıdır';
        }
        $pdo = self::db();
        // Check existing
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return 'Bu e-posta adresi zaten kayıtlı';
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, phone, city, gender) VALUES (?,?,?,?,?,?)');
        $ok = $stmt->execute([$name, $email, $hash, $phone, $city, $gender]);
        if ($ok) {
            return true;
        }
        return 'Kayıt başarısız. Lütfen daha sonra tekrar deneyin.';
    }

    /**
     * Giriş denemesi, başarılı olduğunda true, başarısız olduğunda hata mesajı döndürür.
     */
    public static function login(string $email, string $password)
    {
        self::startSession();
        $email = strtolower(trim($email));
        $stmt = self::db()->prepare('SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            return 'Geçersiz kimlik bilgileri';
        }
        // Kullanıcıyı oturuma kaydet (en az bilgi)
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        return true;
    }

    /**
     * Kullanıcı oturumunu sonlandırır
     */
    public static function logout(): void
    {
        self::startSession();
        session_unset();
        session_destroy();
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::user()) {
            header('Location: login.php');
            exit;
        }
    }

    // Yönetici rolüne sahip giriş yapmış kullanıcı gerektirir
    public static function requireAdmin(): void
    {
        self::requireLogin();
        $user = self::user();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            header('Location: ../index.php');
            exit;
        }
    }
}
?>
