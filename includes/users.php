<?php
// includes/users.php
// Kullanıcı yönetimi sınıfı
require_once __DIR__ . '/db.php';

class Users
{
    private static function db(): PDO
    {
        return (new Database())->getConn();
    }

    public static function getAll(): array
    {
        $stmt = self::db()->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function get(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function setStatus(int $id, string $status): bool
    {
        // status sütunu varsayılır (aktif, yasaklı)
        $stmt = self::db()->prepare('UPDATE users SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public static function delete(int $id): bool
    {
        // Not: Kullanıcı eşyaları/iletileri için kademeli silme veya yabancı anahtar kısıtlamalarını göz önünde bulundurun.
        // Bu işlem, kullanıcı silindiğinde kullanıcının eşyalarının ve iletilerinin de silinmesini sağlar.
        $stmt = self::db()->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
