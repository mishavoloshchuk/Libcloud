<?php 
	require($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	$data = $_POST;
	$login = $data['login'];
	$mail = $data['mail'];
	$password = $data['password'];
	$password_2 = $data['repeatPassword'];
	$errors = array();

	if (isset($data['done'])){
		if (trim($login)==''){ $errors[] = "Введіть ім'я!"; }
		if ($password == ''){ 	$errors[] = "Введіть пароль!"; }
		if ($password_2 !== $password){ $errors[] = "Паролі не збігаються!"; }
		if ($conn->query("SELECT COUNT(*) AS 'cc' FROM `users` WHERE `login` = '$login'")->fetch_assoc()['cc'] > 0) {$errors[] = "Користувач з таким іменем вже зареєстрований. Придумайте інше ім'я.";}
		if ($mail){ // If mail has not been entered
			if ($conn->query("SELECT COUNT(*) AS 'mc' FROM `users` WHERE `mail` = '$mail'")->fetch_assoc()['mc'] > 0){$errors[] = "Користувач з такою поштою вже зареєстрований.";}
		}
		if (!$errors){
			$password = password_hash($password, PASSWORD_DEFAULT);
			$conn->query("INSERT INTO `users` (`login`, `password`, `mail`) VALUES ('$login', '$password', '$mail');");
			header("location: /auth.php");
		} else {
			echo $errors[0];
		}
	}
?>
<!DOCTYPE html>
<html lang="ua">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="/styles/main.css">
	<title>LibCloud - Реєстрація</title>
</head>

<body>
	<!-- Header -->
	<? require($_SERVER['DOCUMENT_ROOT'].'/elements/header.php'); ?>

	<div class="centerbox-wrapper">
		<form class="centerbox" action="" method="POST">
			<h2>Реєстрація</h2>
			<h3 class="error_text_h3"><? echo $errors[0]; ?></h3>
			<br>
			<input required type="text" name="login" placeholder="Ім'я"><br><br>
			<input type="text" name="mail" placeholder="E-mail(Не обов'язково)"><br><br>
			<input required type="password" name="password" placeholder="Пароль"><br><br>
			<input required type="password" name="repeatPassword" placeholder="Повторіть пароль"><br><br>
			<button name="done">Зареєструватися</button>
		</form>	
	</div>

	<!-- Footer -->
	<? require("elements/footer.php"); ?>
</body>
</html>