<?php
$servername = "localhost";
$username   = "u624785608_admin";
$password   = "Coxs000223";
$dbname     = "u624785608_nicklauss";

try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("SET time_zone = '-06:00'");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
