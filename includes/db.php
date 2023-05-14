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

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dataBaseName";
if ($conn->query($sql) !== true){
	echo "Error creating database: " . $conn->error;
}

$conn->select_db($dataBaseName);


// Add standart genres
if (!mysqli_fetch_assoc($conn->query("SELECT COUNT(*) FROM `genres`"))['COUNT(*)']){
	$sql = "INSERT INTO `genres` (`genreName`) VALUES ('Фантастика'),('Детективи і бойовики'),('Любовні Романи'),('Поезія і Драматургія'),('Пригоди'),('Проза'),('Довідкова література'),('Гумор');";

	if ($conn->query($sql) !== true){
		echo $conn->error;
	}
}

?>