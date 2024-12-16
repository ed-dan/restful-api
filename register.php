<?php

require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/register-form.html";

use \config\Database;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $database = new Database($_ENV["DB_HOST"],
                             $_ENV["DB_NAME"],
                             $_ENV["DB_USER"],
                             $_ENV["DB_PASS"]);
                             
    $conn = $database->getConnection();
    
    $sql = "INSERT INTO users (name, username, password_hash, api_key)
            VALUES (:name, :username, :password_hash, :api_key)";
            
    $stmt = $conn->prepare($sql);
    
    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $api_key = bin2hex(random_bytes(16));
    
    $stmt->bindValue(":name", $_POST["name"], PDO::PARAM_STR);
    $stmt->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
    $stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
    $stmt->bindValue(":api_key", $api_key, PDO::PARAM_STR);
    
    $stmt->execute();
    
    echo "Thank you for registering. Your API key is ", $api_key;
    exit;
}

?>

        
        
        
        
        
        
        
        
        
        
        
        
        