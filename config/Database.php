<?php
require_once __DIR__.'/env.php';

loadEnv(__DIR__ . '/../.env');

// var_dump(getenv('DB_USERNAME'));
// $host = getenv('DB_HOST');

class Database{

    private $conn;

    public function connect(){
         $host = getenv('DB_SERVERNAME');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');
        $db_name = getenv('DB_NAME');

        // $this->conn = new mysqli($this->host, $this->username, $this->password);
         $this->conn = new mysqli($host, $username, $password, $db_name);
        if ($this->conn->connect_error) {
            die(
                json_encode([
                    "error" => "Connection failed",
                    "details" => $this->conn->connect_error
                ]));
        }

        $this->conn->query("CREATE DATABASE IF NOT EXISTS {$db_name}");
        $this->conn->select_db($db_name);
        $this->createTable();
        return $this->conn;
    }
    
    public function createTable(){
        $query = "
            CREATE TABLE IF NOT EXISTS countries(
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                capital VARCHAR(255) DEFAULT NULL,
                region VARCHAR(255) DEFAULT NULL,
                population BIGINT NOT NULL,
                currency_code VARCHAR(10) DEFAULT NULL,
                exchange_rate DECIMAL(15,2) DEFAULT NULL,
                estimated_gdp DECIMAL(20,2) DEFAULT NULL,
                flag_url VARCHAR(255) DEFAULT NULL,
                last_refreshed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
            )
        ";
        if(!$this->conn->query($query)){
            die(
                json_encode([
                    "error"=>"An error occured",
                    "details"=>$this->conn->error
                ], JSON_UNESCAPED_UNICODE)
            );
        }
    }
}
