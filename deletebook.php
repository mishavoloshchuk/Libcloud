<?php require_once($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$error = false;


$id = $_POST['delete'];
$book = $conn->query("SELECT * FROM `books` WHERE `id` = '$id'")->fetch_assoc();
($loggedUser['status'] == "Admin" && $loggedUserLogin == $book['postAuthor']) || $error = "no_permision";

if (!$error){
	$url = $book['url'];
	$imgurl = $book['coverimg'];
	@unlink($_SERVER['DOCUMENT_ROOT'].$url);
	@unlink($_SERVER['DOCUMENT_ROOT'].$imgurl);
	$conn->query("DELETE FROM `books` WHERE `books`.`id` = '$id'");
}
header("location: /profile.php?menu=" . urlencode("Додані книги"));
?>