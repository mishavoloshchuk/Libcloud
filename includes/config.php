<?php 
	$config = array(
		'db' => array(
				'server' => 'localhost',
				'user' => 'mysql',
				'password' => 'mysql',
				'database_name' => 'LibCloud'
			)
	);

require($_SERVER['DOCUMENT_ROOT']."/includes/db.php");
require($_SERVER['DOCUMENT_ROOT']."/includes/logged_user.php");
