<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php'); 
require_once("includes/kniga_tools.php");

if (isset($_POST['deleteCollection'])){
	$delClctNam = $_POST['deleteCollection'];
	$conn->query("DELETE FROM `collections` WHERE `id` = '$delClctNam'");
}
?>
<!DOCTYPE html>
<html lang="ua">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="styles/main.css">
	<title>LibCloud</title>
</head>


<body>
	<!-- Header -->
	<? require($_SERVER['DOCUMENT_ROOT'].'/elements/header.php'); ?>

	<div class="golovna">
		<? $menu = $_GET['menu'] ? $_GET['menu'] : 'Історія переглянутих книг'; ?>
		<div class="janr pforile_menus">
			<? if ($status == 'Admin'): ?>
			<div class="block" <? echo $menu == 'Додані книги' ? 'activ' : ''?>><a class="menu_nav_link" href='?menu=Додані книги'><i class="fa-solid fa-book"></i> Додані книги</a></div>
			<? endif ?>
			<div class="block" <? echo $menu == 'Зараз читаю' ? 'activ' : ''?>><a class="menu_nav_link" href='?menu=Зараз читаю'><i class="fa-solid fa-book-open"></i> Зараз читаю</a></div>
			<div class="block" <? echo $menu == 'Прочитані книги' ? 'activ' : ''?>><a class="menu_nav_link" href='?menu=Прочитані книги'><i class="fa-solid fa-check"></i> Прочитані книги</a></div>
			<div class="block" <? echo $menu == 'Історія переглянутих книг' ? 'activ' : ''?>><a class="menu_nav_link" href='?menu=Історія переглянутих книг'><i class="fa-solid fa-clock-rotate-left"></i> Історія переглянутих книг</a></div>
			<div class="block" <? echo $menu == 'Мої колекції' ? 'activ' : ''?>><a class="menu_nav_link" href='?menu=Мої колекції'><i class="fa-solid fa-layer-group"></i> Мої колекції</a></div>
		</div>
		<div class="knigi" id='knigi'>
			<center><h1><?php echo $menu ?>:</h1></center>
			<br><hr><br>
			<div class="booksGrid">
				<?php 
				$flim = 0; $lim = 10;

				$sql = $conn->query("SELECT * FROM `books` WHERE `id` = '0'");

				switch($menu){
					case 'Додані книги':
						$sql = $conn->query("SELECT * FROM books WHERE `postAuthor` = '$loggedUserLogin' ORDER BY `date` DESC LIMIT $flim, $lim");
						break;
					case 'Зараз читаю':
						$sql = $conn->query("SELECT books.* FROM readnow LEFT JOIN books ON bookId = books.id WHERE `userId` = '$loggedUserId'");
						break;
					case 'Прочитані книги':
						$sql = $conn->query("SELECT books.* FROM readdone LEFT JOIN books ON bookId = books.id WHERE `userId` = '$loggedUserId'");
						break;
					case 'Історія переглянутих книг':
						$sql = $conn->query("SELECT viewhistory.date as 'viewDate', books.* from viewhistory LEFT JOIN books on bookId = books.id WHERE `userId` = '$loggedUserId' ORDER BY `viewDate` DESC");
						break;
					case 'Мої колекції':
						$sql = $conn->query("SELECT collections.*, users.login as 'authorLogin' FROM `collections` LEFT JOIN users ON userId = users.id WHERE `userId` = '$loggedUserId'");
						break;
				}

				if ($_GET['search']){
					$search = $_GET['search'];
					$sql = "SELECT * FROM `books` WHERE ((`description` LIKE '%$search%') OR (`name` LIKE '%$search%') OR (`author` LIKE '%$search%') OR (`date` LIKE '%$search%')) AND (`postAuthor` = '$loggedUserLogin') ORDER BY `comments` DESC LIMIT $flim, $lim";
					$sql = $conn->query($sql);
				}

				while ($book = $sql->fetch_assoc()){ 
					$book_id = $book['id'];
					?>
					<div class="kniga <? echo $_GET['menu'] == 'Мої колекції'?'collectionItem':''?>" onclick="document.location.href = '/<? if ($_GET['menu'] == 'Мої колекції') { echo "?collection=$book_id"; } else {echo 'kniga.php/?id='.$book_id;} ?>'">
						<div class="kniga1">
							<div class="info">
								<? if ($_GET['menu'] == 'Мої колекції'): 
									$collection_id = $book['id'];
									$books_count = $conn->query("SELECT COUNT(*) FROM `books-collections` WHERE `collectionId` = '$collection_id'");
									$books_count = $books_count ? $books_count->fetch_assoc()['COUNT(*)'] : 0;
									$books_in_collection = $conn->query("SELECT books.coverimg FROM `books-collections` INNER JOIN books on bookId = `books`.id WHERE `books-collections`.`collectionId` = '$collection_id' LIMIT 4");
									?>
									<div class="collection_books_covers" <? if ($books_count <= 1) { echo "count=1"; }?>>
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
									<h3><?php echo $book['name'];?></h3><br>
									<div class="kniga_about">
										<dd class="kniga_item_about_author"><?php echo implode(", ", getBookAuthors($book_id));?></dd>
										<dd class="kniga_item_about_genre"><?php echo implode(", ", getBookGenres($book_id));?></dd>
										<dd><?php echo $book['date'];?></dd>
									</div>
								<? endif ?>
							</div><br>
							<? if ($menu == 'Додані книги'): ?>
							<form class="button" method="POST" action="deletebook.php" onSubmit="return confirm('Ви впевнені, що хочете видалити книгу?');">
								<button style="width: 100%;" name='delete' value="<?php echo $book_id;?>" class='deleteBut' onclick="event.stopPropagation()">Видалити</button>
							</form>
							<? endif ?>
							<? if ($menu == 'Мої колекції'): ?>
							<form class="button" method="POST" onSubmit="return confirm('Ви впевнені, що хочете видалити колекцію?');">
								<button style="width: 100%;" name='deleteCollection' value="<?php echo $collection_id;?>" class='deleteBut' onclick="event.stopPropagation()">Видалити</button>
							</form>
							<? endif ?>
						</div>
					</div>		
				<?php }
				?>	
			</div>
		</div>
	</div>

	<!-- Footer -->
	<? require("elements/footer.php"); ?>
</body>

</html>