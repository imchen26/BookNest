<?php
$host = 'localhost';      
$port = 3306;            
$dbname = 'booknest';   
$username = 'root';      
$password = 'Dlsu1234!';           

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
