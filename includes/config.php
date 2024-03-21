<?php 

$config = [
	'db' => [
		'server' => 'localhost',
		'user' => 'root',
		'password' => '',
		'database_name' => 'LibCloud'
	]
];

require($_SERVER['DOCUMENT_ROOT']."/includes/db.php");
require($_SERVER['DOCUMENT_ROOT']."/includes/logged_user.php");
