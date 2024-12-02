<?php
// includes/db.php
$dsn = 'mysql:host=localhost;dbname=help_portal';
$username = 'stud';
$password = 'stud';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>