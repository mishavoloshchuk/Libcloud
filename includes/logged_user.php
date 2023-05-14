<?php
if ($_COOKIE['logged_user_id']){
	$_SESSION['logged_user_id'] = $_COOKIE['logged_user_id'];
}
$loggedUserId = $_SESSION['logged_user_id'];
$loggedUser = $conn->query("SELECT * FROM `users` WHERE `id` = '$loggedUserId'")->fetch_assoc();
$loggedUserLogin = $loggedUser['login'];
unset($loggedUser['password']); // Hide password
?>