<?php 
session_start();

if ($_COOKIE['user']){$_SESSION['user'] = $_COOKIE['user'];}

$servername = $config['db']['server'];
$username = $config['db']['user'];
$password = $config['db']['password'];
$dataBaseName = $config['db']['database_name'];

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 

$conn->set_charset("utf8mb4");

$conn->select_db($dataBaseName);

?>