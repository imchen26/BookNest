<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'booknest';
$port = 3306;

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
