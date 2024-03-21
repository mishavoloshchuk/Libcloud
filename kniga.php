<?php 
require('includes/config.php'); 
require_once("includes/kniga_tools.php");
$bookId = $_GET['id']; // Book id
//User id
// Foreach books
if ($book = $conn->query("SELECT * FROM books WHERE books.id = '$bookId'")->fetch_assoc()){ // If there is a book by the id
	$date = date("Y.m.d H:i:s"); // Datetime
	$book_id = $book['id']; // Also book id)
	// Add comment to the book
	if (isset($_POST['confirm']) && !$conn->query("SELECT * FROM comments WHERE authorId = $loggedUserId AND bookId = $bookId")->fetch_assoc() ){
		$comment = $_POST['comment']; // Comment text
		$stars = $_POST['confirm']; // Count of stars
		$sql = "INSERT INTO `comments` (`bookId`, `authorId`, `text`, `stars`) VALUES ('$bookId', '$loggedUserId', '$comment', '$stars');";
		$conn->query($sql); // Add comment to database
		$bookComments = $conn->query("SELECT COUNT(*) AS 'comments' FROM `comments` WHERE `bookId` = '$bookId'")->fetch_assoc()['comments'];
		$sql = "UPDATE `books` SET `comments` = '$bookComments' WHERE `id` = '$bookId'";
		$conn->query($sql); // Update the comments count on the book
	}
	// Delete comment from the book
	if (isset($_POST['deleteCommentId'])){
		$commentId = $_POST['deleteCommentId']; // Get comment ID
		$conn->query("DELETE FROM `comments` WHERE `id` = '$commentId'");
	}
	// Mark book as Readed
	if (isset($_POST['addToReadDone'])){
		if ($readdoneid = $conn->query("SELECT * FROM `readdone` WHERE `bookId` = '$book_id' AND `userId` = '$loggedUserId'")->fetch_assoc()['id']){
			$sql = "DELETE FROM `readdone` WHERE `readdone`.`id` = '$readdoneid'";
			$conn->query($sql);
		} else {
			$sql = "INSERT INTO `readdone` (`bookId`, `userId`) VALUES ('$book_id', '$loggedUserId');";
			$conn->query($sql);
		}
	}
	// Mark book as Read now
	if (isset($_POST['addToReadNow'])){
		if ($readnow = $conn->query("SELECT * FROM `readnow` WHERE `bookId` = '$book_id' AND `userId` = '$loggedUserId'")->fetch_assoc()['id']){
			$sql = "DELETE FROM `readnow` WHERE `readnow`.`id` = '$readnow'";
			$conn->query($sql);
		} else {
			$sql = "INSERT INTO `readnow` (`bookId`, `userId`) VALUES ('$book_id', '$loggedUserId');";
			$conn->query($sql);
		}
	}
	// Add book to collection
	if (isset($_POST['addToCollection'])){
		$collectionName = $_POST['addToCollection'];
		if ($_POST['collection'] == 'newCollection') {
			if (!$conn->query("SELECT * FROM `collections` WHERE `userId` = '$loggedUserId' AND `name` = '$collectionName'")->fetch_assoc()){
				$conn->query("INSERT INTO `collections` (`name`, `userId`) VALUES ('$collectionName', '$loggedUserId')");
			}
			// Get new collection ID
			$collectionId = $conn->query("SELECT `id` FROM `collections` WHERE `userId` = '$loggedUserId' AND `name` = '$collectionName'")->fetch_assoc()['id']; 
		} else {
			// Get collection ID
			$collectionId = $_POST['collection'];
		}
		if (!$conn->query("SELECT `id` FROM `books-collections` WHERE `bookId` = '$book_id' AND `collectionId` = '$collectionId'")->fetch_assoc()){
			$conn->query("INSERT INTO `books-collections` (`bookId`, `collectionId`) VALUES ('$book_id', '$collectionId')");
		}
	}
	// Add the book to view history
	if ($hii = $conn->query("SELECT * FROM `viewhistory` WHERE `userId` = '$loggedUserId' AND `bookId` = '$book_id'")->fetch_assoc()['id']){ // If already in view history
		$conn->query("UPDATE `viewhistory` SET `date` = '$date' WHERE `viewhistory`.`id` = '$hii'");
	} else {
		$conn->query("INSERT INTO `viewhistory` (`bookId`, `userId`) VALUES ('$book_id', '$loggedUserId')");
	}
} else { // If there is no book by the id
	header("location: /");
}

function minsToTime($mins){
	$str = '';
	$mins =  (int) filter_var($mins, FILTER_SANITIZE_NUMBER_INT); // Extract number from string

	$minutes = $mins % 60;
	$hours = floor($mins / 60);

	$hours && $str = $str . $hours." год";
	($minutes || !$str) && $str = $str . " " . $minutes." хв ";
	return $str;
}
?>
<!DOCTYPE html>
<html lang="ua">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../styles/main.css">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital@1&display=swap" rel="stylesheet"> 
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@700&display=swap" rel="stylesheet">  
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital@1&display=swap" rel="stylesheet"> 
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Fjalla+One&display=swap" rel="stylesheet"> 
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Oswald&display=swap" rel="stylesheet"> 
	<script type="text/javascript">
window.onload = function(){
	const stars = document.getElementById('stars');
	const starsCount = 5;
	if (stars){
		stars.addEventListener('click', function(e){
			let starId = +e.target.getAttribute('star_number');
			if (typeof starId == 'number'){
				for (let i = starsCount; i--;){
					if (i <= starId){
						document.getElementById('stars').children[i].src = '../ico/starActiv.png';
					} else {
						document.getElementById('stars').children[i].src = '../ico/star.png';
					}			
				}
				sendComment.value = starId+1;
			}
		});
	}	
}
	</script>
	<title>LibCloud</title>
</head>
<body>
	<!-- Header -->
	<? require($_SERVER['DOCUMENT_ROOT'].'/elements/header.php'); ?>

	<div class="golovna">
		<!-- Categories -->
		<? require('elements/categories.php'); ?>

		<div class="knigi">
			<div class="golovna-kniga">
				<div class="golovna-kniga1">
					<div class="kniga-img"><img src="<?php echo $book['coverimg'];?>" alt="" width="100%"></div>
						<p class="nazva"><?php echo $book['name'];?></p><br>
						<p>Автор: <span class="textInfo">
							<? $joinSing = ''; foreach (getBookAuthors($bookId) AS $authorId => $authorName):
								echo $joinSing;
								echo "<a href='/?author=$authorId' class='textInfoGenres'>$authorName</a>";
								$joinSing = ", ";
							endforeach; ?>
						</span></p>
						<p>Жанр: <span class="textInfo">
							<? $joinSing = ''; foreach (getBookGenres($bookId) AS $genreId => $genreName):
								echo $joinSing;
								echo "<a href='/?genre=$genreId' class='textInfoGenres'>$genreName</a>";
								$joinSing = ", ";
							endforeach; ?>
						</span></p>
						<p>Рік: <span class="textInfo"><?php echo $book['date'];?></span></p>
						<p>Мова: <span class="textInfo"><?php echo $book['language'];?></span></p>
						<p><?php if ($book['audio'] == 0){echo "Кількість сторінок";}else{echo "Тривалість";}?>: <span class="textInfo"><?php echo $book['audio'] == 0 ? $book['pages'] : minsToTime($book['pages']);?></span></p>
						<p>Кількість відгуків: <span class="textInfo"><?php echo $book['comments'];?></span></p>
						<hr>
						<? if ($loggedUserLogin): ?>
							<!-- Mark as Now read -->
							<form action="#" method="POST">
							<? if ($conn->query("SELECT * FROM `readnow` WHERE `bookId` = '$book_id' AND `userId` = '$loggedUserId'")->fetch_assoc() ): ?>
								<button name="addToReadNow" class="mark_book_as" style="background-color: rgba(0,160,185,0.8); color: white;">Зараз 	читаю</button>
							<? else: ?>
									<button name="addToReadNow" class="mark_book_as">Позначити як "Зараз читаю"</button>
							<? endif ?>

							<!-- Mark as Readed -->
							<? if ($conn->query("SELECT * FROM `readdone` WHERE `bookId` = '$book_id' AND `userId` = '$loggedUserId'")->fetch_assoc() ): ?>
								<button name="addToReadDone" class="mark_book_as" style="background-color: rgba(37,185,0,0.8); color: white;">	Прочитано ✔</button>
							<? else: ?>
									<button name="addToReadDone" class="mark_book_as">Позначити як "Прочитано"</button>
							<? endif ?>
							</form>

							<!-- Add the book to a collection -->
							<form action="#" method="POST">
								<span>Додати книгу до колекції: </span>
								<select name="collection" id='collection_select'>
									<option value="newCollection">Нова колекція</option>
									<? 
									$sql = $conn->query("SELECT * FROM `collections` WHERE `userId` = '$loggedUserId'");
									while ( $clct = $sql->fetch_assoc() ):
										$clct_id = $clct['id']; // Collection ID
										$optionDisabled = $conn->query("SELECT * FROM `books-collections` WHERE bookId = $book_id AND collectionId = $clct_id")->fetch_assoc() ? 'disabled' : ''; // Disable option if the book is already in this collection

									 ?>
										<option value="<?echo $clct['id']?>" <? echo $optionDisabled; ?> ><?echo $clct['name']; echo $optionDisabled?' ✔':'';?></option>
									<? endwhile ?>
								</select>
								<button name="addToCollection" onclick="addCollectionHandler(event)" id="addCollectionButton">Додати</button>
							</form>
						<? endif ?>
						<br>
						
						<h3 class="opu">Опис книги:</h3>
						<p style="font-size: 1.2rem; white-space: pre-wrap; color: #FFFA"><?php echo $book['description'];?></p>
				</div>
				<div class="read">
					<br>
					<button style="padding: 15px 25px;" onclick="window.open(<?php echo "'"; if ($loggedUserLogin){echo $book['url'];}else{echo '/auth.php';}; echo "'";?>)"><?php if ($book['audio']==0){echo "Читати книгу";}else{echo "Слухати книгу";}?></button>
					<br><br>
				</div>
			</div>
			<?php 
			// Leave a comment
			if ($loggedUserLogin){
				$loggedUserId = $conn->query("SELECT * FROM `users` WHERE `login` = '$loggedUserLogin'")->fetch_assoc()['id'];;
				$comm = $conn->query("SELECT * FROM `comments` WHERE `authorId` = '$loggedUserId' AND `bookId` = '$bookId'")->fetch_assoc();
				// If user already has a comment
				if ($comm){ ?>
					<div class="coment">
						<p class="q">Дякуємо за відгук! Ваш відгук:</p>
						<div class="comentar">
							<span class="txt"><?php echo $comm['text'];?></span>
						</div><br>
						<p id='stars'>
							<?php  // Stars icons
							for ($i = 0; $i < 5; $i++){
								if ($i < $comm['stars']){?>
									<img src="../ico/starActiv.png">
								<?php } else {?>
									<img src="../ico/star.png">
								<?php }
							}
							?>
						</p><br>
						<form class="button" method="POST" onSubmit="return confirm('Ви впевнені, що хочете видалити відгук?');">
							<button style="padding: 10px 15px" name='deleteCommentId' value="<?php echo $comm['id'];?>" class='deleteBut' onclick="event.stopPropagation()">Видалити відгук</button>
						</form>
					</div>
				<?php } else {?>
					<form class="coment" method="POST">
						<p class="q">Залиште відгук про цю книгу:</p>
						<p id='stars'>
							<img src="../ico/star.png" star_number='0'>
							<img src="../ico/star.png" star_number='1'>
							<img src="../ico/star.png" star_number='2'>
							<img src="../ico/star.png" star_number='3'>
							<img src="../ico/star.png" star_number='4'>
						</p>
						<div class="comentar">
							<textarea required placeholder="Ваш відгук" name='comment'></textarea>
							<p><button name='confirm' value="4" id='sendComment'>Відправити</button></p>
						</div>
					</form>
				<?php }
			} else { ?>
					<div class="coment">
						<br>
						<p class="q"><a style="color: #446fc1;" href="/auth.php">Увійдіть</a> або <a style="color: #446fc1;" href="/register.php">зареєструйтесь</a>, щоб залишити відгук</p>
						<br>
					</div>
			<?php }
			?>
			<br><br>
			<div class="comentari">
				<center><p class="q"><?php echo( mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS 'count' FROM `comments` WHERE `bookId` = $bookId"))['count']?'Відгуки':'Немає відгуків')?></p></center>
				<?php
				$sql = $conn->query("SELECT comments.*, login, status FROM `comments` LEFT JOIN users ON authorId = users.id WHERE `bookId` = '$bookId'");

				while($comm = $sql->fetch_assoc()){
				?>
				<br>
				<div class="comentarrr">
					<p class="user" style="<? echo $comm['status'] == 'Admin' ? 'color: #D51E1E; font-weight: bold;' : '' ?>"><?php echo $comm['login'];?></p>
					<p class="date"><?php echo $comm['date'];?></p>
					<p name='stars'>
						<?php 
						for ($i = 0; $i < 5; $i++){
							if ($i < $comm['stars']){?>
								<img src="../ico/star1Activ.png">
							<?php } else {?>
								<img src="../ico/star1.png">
							<?php }
						}
						?>
					</p>
					<p class="txt"><?php echo $comm['text'];?></p>
					<? if ($loggedUserId == $comm['authorId'] || $status == 'Admin'): ?>
						<form class="button delCommentButton" method="POST" onSubmit="return confirm('Ви впевнені, що хочете видалити відгук?');">
							<button style="width: 100%;" name='deleteCommentId' value="<?php echo $comm['id'];?>" class='deleteBut' onclick="event.stopPropagation()">Видалити</button>
						</form>
					<? endif; ?>
				</div>
				<?php };?>
				<br>
			</div>


		</div>
	</div>

	<!-- Footer -->
	<? require("elements/footer.php"); ?>

	<script type="text/javascript">
	// Add collection handler
	function addCollectionHandler (event){
		if (collection_select.value === 'newCollection'){
			let collection_name = prompt('Назва колекції', 'Моя колекція');
			if (collection_name){
				addCollectionButton.value = collection_name;
			} else {
				event.preventDefault();
			}
		}
	}
	</script>
</body>

</html>