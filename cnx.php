<?php
class Connexion {
    private $host = 'localhost';    
    private $dbname = 'projet'; 
    private $username = 'root'; 
    private $password = '';  
    private $port = '3306'; 
    //private $port = '3308';
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=localhost;port=3306;dbname=projet",
                //"mysql:host=localhost;port=3308;dbname=projet",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function getConnexion() {
        return $this->conn;
    }
}
?>
