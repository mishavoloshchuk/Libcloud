<?php require_once('includes/config.php');
	require_once("includes/kniga_tools.php");

	// Delete book from the collection
	if (isset($_POST['deleteBookId'])){
		$collectionBookId = $_POST['deleteBookId'];
		$collectionId = $_POST['collectionId'];

		$deleteCollection = $conn->query("SELECT * FROM collections WHERE id = $collectionId")->fetch_assoc();

		$error = false;

		if ($loggedUserId == $deleteCollection['userId']) {
			$conn->query("DELETE FROM `books-collections` WHERE bookId = $collectionBookId AND collectionId = $collectionId");

			// Delete collection if that was the last book in the collection
			$count = $conn->query("SELECT COUNT(*) AS 'count' FROM `books-collections` WHERE collectionId = $collectionId")->fetch_assoc()['count'];
			if ($count == 0){
				$conn->query("DELETE FROM `collections` WHERE id = $collectionId");
			}
		}
	}

	$flim = 0; $lim = 256;

	$qBooks = $conn->query("SELECT books.* FROM books ORDER BY comments DESC LIMIT $flim, $lim");

	if ($_GET['sort'] == 'new'){
		$qBooks = $conn->query("SELECT books.* FROM books ORDER BY `date` DESC LIMIT $flim, $lim");
	} else
	if ($_GET['sort'] == 'audiobooks'){
		$qBooks = $conn->query("SELECT books.* FROM books WHERE `audio` = 1 ORDER BY `date` DESC LIMIT $flim, $lim");
	} else
	if ($_GET['sort'] == 'topaudio'){
		$qBooks = $conn->query("SELECT books.* FROM books WHERE `audio` = 1 ORDER BY `comments` DESC LIMIT $flim, $lim");
	} else
	if ($_GET['sort'] == 'collections'){
		$qBooks = $conn->query("SELECT collections.*, login FROM `collections` LEFT JOIN users ON collections.userId = users.id ORDER BY `date`");
	} else
	if ($_GET['collection']){
		$collection_id = $_GET['collection'];
		$collection = $conn->query("SELECT * FROM `collections` WHERE `id` = '$collection_id'")->fetch_assoc();
		if (!$collection) header("location: /");
		$qBooks = $conn->query("SELECT books.* FROM `books-collections` LEFT JOIN books ON `books-collections`.bookId = books.id WHERE `collectionId` = $collection_id");
	} else
	if ($_GET['genre']){
		$genre = $_GET['genre'];
		$qBooks = $conn->query("SELECT books.* FROM `books-genres` INNER JOIN books ON bookId = books.id WHERE `genreId` = '$genre' ORDER BY `comments` DESC LIMIT $flim, $lim");
	} else
	if ($_GET['search']){
		$search = $_GET['search'];
		$qBooks = "SELECT books.* FROM `books` WHERE (`description` LIKE '%$search%') OR (`name` LIKE '%$search%') OR (`author` LIKE '%$search%') OR (`date` LIKE '%$search%') ORDER BY `comments` DESC LIMIT $flim, $lim";
		$qBooks = $conn->query($qBooks);
	}
?>
<!DOCTYPE html>
<html lang="ua">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="styles/main.css">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital@1&display=swap" rel="stylesheet"> 
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@700&display=swap" rel="stylesheet">  
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital@1&display=swap" rel="stylesheet"> 
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap" rel="stylesheet"> 

	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Oswald&display=swap" rel="stylesheet"> 
	<title>LibCloud</title>
</head>


<body>
	<!-- Header -->
	<? require($_SERVER['DOCUMENT_ROOT'].'/elements/header.php'); ?>

	<div class="golovna">
		<!-- Categories -->
		<? require('elements/categories.php'); ?>

		<div class="knigi">
			<div class="menuu">
				<div class="nemuuu"><div class="nemuuu_inner"><a class="menu_nav_link" href='/?sort=new'>Новинки <i class="fa-solid fa-bolt"></i></a></div></div>
				<div class="nemuuu"><div class="nemuuu_inner"><a class="menu_nav_link" href='/?sort=top'>Топ книг <i class="fa-solid fa-star"></i></a></div></div>
				<div class="nemuuu"><div class="nemuuu_inner"><a class="menu_nav_link" href='/?sort=topaudio'>Топ аудіокниг <i class="fa-solid fa-play"></i></a></div></div>
				<div class="nemuuu"><div class="nemuuu_inner"><a class="menu_nav_link" href='/?sort=audiobooks'>Аудіокниги <i class="fa-solid fa-headphones"></i></a></div></div>
				<div class="nemuuu"><div class="nemuuu_inner"><a class="menu_nav_link" href='/?sort=collections'>Колекції користувачів <i class="fa-solid fa-layer-group"></i></a></div></div>
			</div>

			<? if ($collection): ?>
				<!-- Collection title -->
				<br><h1>Колекція: «<?echo $collection['name']?>»</h1><br>
			<? elseif ($search && !$qBooks->num_rows): ?>
				<!-- Empty seach result -->
				<br><h1>Не знайшлося книг за вашим запитом😥</h1><br>
			<? elseif (!$qBooks->num_rows): ?>
				<!-- There's nothing to show yet -->
				<br><h1>Поки що, нічого немає...</h1><br>
			<? endif; ?>
			<div class="booksGrid">
				<? 
				if ($qBooks):
					while ($book = $qBooks->fetch_assoc()):
						$id = $book['id'];
						?>
						<div class="kniga <? echo $_GET['sort'] == 'collections'?'collectionItem':''?>" onclick="document.location.href = '/<? if ($_GET['sort'] == 'collections') { echo '?collection='.$book['id']; } else {echo 'kniga.php/?id='.$id;} ?>'">
							<div class="innerKniga">
								<? if ($_GET['sort'] == 'collections'): // Display collections or books
									$collection_id = $book['id'];
									$books_count = $conn->query("SELECT COUNT(*) FROM `books-collections` WHERE `collectionId` = '$collection_id'");
									$books_count = $books_count ? $books_count->fetch_assoc()['COUNT(*)'] : 0;
									$books_in_collection = $conn->query("SELECT books.coverimg FROM `books-collections` INNER JOIN books on bookId = `books`.id WHERE `books-collections`.`collectionId` = '$collection_id' LIMIT 4");
									?>
									<div class="collection_books_covers" <? echo "count=".$books_count;?>>
										<? while ($coverurl = $books_in_collection->fetch_assoc()['coverimg']) {
											echo ("<img src=\"$coverurl\" alt='Collection book image'>");		
										}
										?>	
									</div>
									<h3 style="text-align:center;"><?php echo $book['name'];?></h3>
									<div class="kniga_about">
										<dd> Автор: <?php echo $book['login'];?></dd>
										<dd> Кількість книг: <?php echo $books_count;?></dd><br>
										<dd class="date"> <?php echo $book['date'];?></dd>
									</div>
								<? else: ?>
									<img src="<?php echo $book['coverimg'];?>">
									<br>
									<h3 class="kniga_title"><?php echo $book['name'];?></h3>
									<div class="kniga_about">
										<dd class="kniga_item_about_author"><?php echo $book['author'];?></dd>
										<dd class="kniga_item_about_genre"><?php echo implode(", ", getBookGenres($id));?></dd>
										<dd><?php echo $book['date'];?></dd>
									</div>
									<? if ($_GET['collection'] && $collection['userId'] == $loggedUserId):?>
										<form action="#" class="button" method="POST" onSubmit="return confirm('Ви впевнені, що хочете видалити книгу з колекції?');">
											<input hidden name="collectionId" value="<?echo $collection['id'];?>">
											<button style="width: 100%;" name='deleteBookId' value="<?php echo $book['id'];?>" class='deleteBut' onclick="event.stopPropagation()">Видалити</button>
										</form>		
									<? endif ?>
								<? endif ?>
							</div>
						</div>		
					<? endwhile;
				endif;
				?>
			</div>
		</div>
	</div>

	<!-- Footer -->
	<? require("elements/footer.php"); ?>
</body>

</html>