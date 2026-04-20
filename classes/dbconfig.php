<?php
class Database{
    private $host;
    private $db_name;
    private $username;
    private $password;

    public $conn;

    public function dbConnection(){
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: '';
        $this->username = getenv('DB_USERNAME') ?: '';
        $this->password = getenv('DB_PASSWORD') ?: '';

        $this->conn = null;
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
        }catch(PDOException $exception){
            $errorMsg = "Connection error: " . $exception->getMessage();
            echo $errorMsg;
        }
        return $this->conn;
    }
}
?>