<?php require($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	$data = $_POST;
	$errors = array();
	$mailorlog = $data['mailorlog'];
	$password = $data['password'];
	@$user = $conn->query("SELECT * FROM `users` WHERE (`login` = '$mailorlog') OR (`mail` = '$mailorlog')")->fetch_assoc();
	if (isset($data['done'])){
		if (trim($mailorlog)==''){ 	$errors[] = "Введіть ім'я або пошту!"; }

		if ( $password == '' ){ $errors[] = "Введіть пароль!"; }

		if ( empty($errors) ){
			if ($user){
				if (password_verify($password, $user->password) or password_verify($password, $user['password'])){
					$_SESSION['logged_user_id'] = $user['id'];
					if ($data['saveuser'] == 'on'){
						setcookie ( 'logged_user_id' , $user['id'], [
						'expires' => time()+60*60*24*365,
						'path' => '/',
						'samesite' => 'Lax',
					]);
					}
					header("location: /");
				} else {
					$errors[] = 'Перевірте правильність введених даних';
				}
			} else {
				$errors[] = 'Перевірте правильність введених даних';
			}
		}
	}

?>
<!DOCTYPE html>
<html lang="ua">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="/styles/main.css">
	<title>LibCloud - Авторизація</title>
</head>

<body>
	<!-- Header -->
	<? require($_SERVER['DOCUMENT_ROOT'].'/elements/header.php'); ?>
	<div class="centerbox-wrapper">	
		<form class="centerbox" action="" method="POST">
			<h2>Авторизація</h2>
			<h3 class="error_text_h3"><? echo $errors[0]; ?></h3>
			<br>
			<input required type="text" name="mailorlog" placeholder="Логін або E-mail"><br><br>
			<input required type="password" name="password" placeholder="Пароль"><br><br>
			<label>Запам'ятати мене:</label>
			<input type="checkbox" name="saveuser"><br><br>
			<button name="done">Авторизуватися</button>
		</form>
	</div>

	<!-- Footer -->
	<? require("elements/footer.php"); ?>
</body>
</html>