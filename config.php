<?php
/*define('DB_Server','175.45.63.58:3306');
    define("DB_username",'tony');
    define("DB_password",'Lunatoys123');
    define("DB_database",'world');
    $db = mysqli_connect(DB_Server,DB_username,DB_password,DB_database);*/

$servername = "192.168.0.103:3306";
$username = "tony";
$password = "Lunatoys123";

try {
    $conn = new PDO("mysql:host=$servername;dbname=world;", $username, $password);
    $conn->exec('SET CHARACTER SET utf8');
    $conn->query("SET NAMES utf8");
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
