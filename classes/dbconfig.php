<?php
/**
 * Database connection — reads environment variables (Dokploy / Docker).
 *
 * Required for production:
 *   DB_HOST     — MySQL hostname. In Docker use the DB service INTERNAL name (Dokploy shows this), NOT "localhost".
 *   DB_NAME     — Database name
 *   DB_USERNAME — MySQL user
 *   DB_PASSWORD — MySQL password
 *
 * Optional:
 *   DB_PORT     — default 3306
 *
 * Also accepts MYSQL_* names (common on MariaDB/MySQL images) if DB_* is not set.
 */
class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;

    public $conn;

    public function dbConnection() {
        $this->host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
        $this->port = (int) (getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306);
        $this->db_name = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: '';
        $this->username = getenv('DB_USERNAME') ?: getenv('MYSQL_USER') ?: '';
        $this->password = getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';

        if ($this->db_name === '' || $this->username === '') {
            throw new RuntimeException(
                'Database is not configured: set DB_NAME and DB_USERNAME (and DB_HOST, DB_PASSWORD). ' .
                'In Dokploy, copy these from your MySQL service and add them to the application environment.'
            );
        }

        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name . ';charset=utf8mb4';

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            $hint = '';
            if (stripos($msg, '2002') !== false || stripos($msg, 'refused') !== false) {
                $hint = ' DB_HOST is wrong or the database is not reachable. On Dokploy/Docker, use the internal database hostname from the database service (not "localhost").';
            }
            throw new RuntimeException('Database connection failed: ' . $msg . $hint, 0, $e);
        }

        return $this->conn;
    }
}
