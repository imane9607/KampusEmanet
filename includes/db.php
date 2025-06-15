<?php
// includes/db.php
// XAMPP yerel sunucu için PDO bağlantı sınıfı
// Yerel kurulumunuza göre $db_name, $username, $password değerlerini düzenleyin.

class Database
{
    private string $host = 'localhost';
    private string $db_name = 'lost_found';
    private string $username = 'root';
    private string $password = '';

    public ?PDO $conn = null;

    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die('Veritabanı bağlantısı başarısız: ' . $e->getMessage());
        }
    }

    public function getConn(): PDO
    {
        if ($this->conn === null) {
            $this->__construct();
        }
        return $this->conn;
    }
}
?>
