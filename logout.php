<?php require_once($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	session_destroy();
	unset($_COOKIE['logged_user_id']);
	setcookie('logged_user_id', null, [
		'expires' => time() + 1,
		'path' => '/',
		'samesite' => 'Lax'
	]);
	header("location: /");
?>