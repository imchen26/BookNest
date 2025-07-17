<?php
$host = 'localhost';                 
$dbname = 'booknest';   
$username = 'root';      
$password = '';           

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
