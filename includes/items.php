<?php
// includes/items.php
// Eşya yönetimi sınıfı
require_once __DIR__ . '/db.php';

class Items
{
    private static function db(): PDO
    {
        return (new Database())->getConn();
    }

    public static function create(int $userId, string $title, string $description, string $category, string $location, string $status, string $date, ?string $imagePath=null): ?int
    {
        $db = self::db();
        $db->beginTransaction();
        $stmt = $db->prepare('INSERT INTO items (user_id, title, description, category, location, status, `date`, created_at) VALUES (?,?,?,?,?,?,?,NOW())');
        if(!$stmt->execute([$userId,$title,$description,$category,$location,$status,$date])){
            $db->rollBack();
            return null;
        }
        $itemId = (int)$db->lastInsertId();
        if($imagePath){
            self::addImage($itemId,$imagePath);
        }
        $db->commit();
        return $itemId;
    }

    public static function getAll(): array
    {
        $stmt = self::db()->query('SELECT i.*, u.name AS reporter FROM items i JOIN users u ON u.id=i.user_id ORDER BY i.created_at DESC');
        return $stmt->fetchAll();
    }

    public static function getByUser(int $userId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function get(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM items WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function update(int $id, int $userId, string $title, string $description, string $category, string $location, string $status, string $date): bool
    {
        $stmt = self::db()->prepare('UPDATE items SET title=?, description=?, category=?, location=?, status=?, `date`=? WHERE id=? AND user_id=?');
        return $stmt->execute([$title,$description,$category,$location,$status,$date,$id,$userId]);
    }

    /**
     * Orijinal sahibinden bağımsız olarak eşyayı güncelle (yönetici yetkisi)
     */
    public static function adminUpdate(int $id, string $title, string $description, string $category, string $location, string $status, string $date): bool
    {
        $stmt = self::db()->prepare('UPDATE items SET title=?, description=?, category=?, location=?, status=?, `date`=? WHERE id=?');
        return $stmt->execute([$title,$description,$category,$location,$status,$date,$id]);
    }

    public static function delete(int $id, int $userId): bool
    {
        $stmt = self::db()->prepare('DELETE FROM items WHERE id=? AND user_id=?');
        return $stmt->execute([$id, $userId]);
    }

    public static function countAll(): int
    {
        $stmt = self::db()->query('SELECT COUNT(*) FROM items');
        return $stmt->fetchColumn();
    }

    public static function countByUser(int $userId): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM items WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM items WHERE status = ?');
        $stmt->execute([$status]);
        return $stmt->fetchColumn();
    }

    public static function countReunited(): int
    {
        return self::countByStatus('reunited');
    }

    public static function addImage(int $itemId, string $path): bool
    {
        return self::db()->prepare('INSERT INTO item_images (item_id, file_name, uploaded_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE file_name=VALUES(file_name), uploaded_at=NOW()')->execute([$itemId, $path]);
    }

    public static function setImage(int $itemId, string $path): bool
    {
        // fetch current image
        $cur = self::firstImage($itemId);
        if ($cur && file_exists($cur)) unlink($cur);
        return self::addImage($itemId, $path);
    }

    public static function firstImage(int $itemId): ?string
    {
        $stmt = self::db()->prepare('SELECT file_name FROM item_images WHERE item_id=? ORDER BY id ASC LIMIT 1');
        $stmt->execute([$itemId]);
        return $stmt->fetchColumn() ?: null;
    }

    public static function updateStatus(int $itemId, string $status): bool
    {
        $stmt = self::db()->prepare('UPDATE items SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $itemId]);
    }

    public static function adminDelete(int $itemId): bool
    {
        $stmt = self::db()->prepare('DELETE FROM items WHERE id = ?');
        return $stmt->execute([$itemId]);
    }

    /**
     * Eşyalar tablosunda mevcut olan benzersiz durumları getirir.
     * @return array<string>
     */
    public static function getStatuses(): array
    {
        $stmt = self::db()->query('SELECT DISTINCT status FROM items ORDER BY status');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Eşyalar tablosunda mevcut olan benzersiz kategorileri getirir.
     * @return array<string>
     */
    public static function getCategories(): array
    {
        $stmt = self::db()->query('SELECT DISTINCT category FROM items ORDER BY category');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
