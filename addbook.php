<?php require($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	$login = $loggedUserLogin; // User login
	$status = $loggedUser['status']; // User status
	$error = false;

	$status == 'Admin' || $error = "Потрібен статус адміністратора!";
	$login || header("location: /auth.php");

	/**
	 * @return True if affected_rows > 0
	 * 
	 * @param JSON data of selected items
	 * @param Example of $tableRepresent
	[
		"right" => ['name' => 'genres', 'value' => 'genreName'], 
		"middle" => ['name' => 'books-genres', 'leftId' => 'bookId', 'rightId' => 'genreId'],
		"leftId" => $createdBook['id']
	]
	*/
	function selectItems ($jsonSelectData, $tableRepresent) {
		$selectData = json_decode($jsonSelectData);
		$tbRp = $tableRepresent;
		$err = false;
		global $conn;

		$genres_ids = array();
		$new_genres = array();

		// Get values
		foreach ($selectData as $id => $genreName){
			if (is_numeric($id)){
				$genres_ids[] = $id;
			} else if ($id == 'new') {
				$new_genres[] = $genreName;
			}
		}
		// Check selected genres
		$genres_ids_string = implode(',', $genres_ids);
		if (!empty($genres_ids)) {
			$inGenre = "SELECT id FROM `".$tbRp['right']['name']."` WHERE id IN ($genres_ids_string)";
			if ($conn->query($inGenre)->num_rows != count($genres_ids)){
				$err = "Неправильні жанри!";
			}
		}

		if (!$err){
			// Check and create new genres
			if (!empty($new_genres)) {
				foreach ($new_genres as $genreName){
					$sqlGenre = $conn->query("SELECT * FROM `".$tbRp['right']['name']."` WHERE `".$tbRp['right']['value']."` = '$genreName'");
					if ($fetchGenre = $sqlGenre->fetch_assoc()) {
					 	in_array($fetchGenre['id'], $genres_ids) || $genres_ids[] = $fetchGenre['id'];
					} else {
						$genres_ids[] = $conn->query("INSERT INTO `".$tbRp['right']['name']."` (`".$tbRp['right']['value']."`) VALUES ('$genreName') RETURNING id")->fetch_assoc()['id'];
					}
				}
			}

			// Set genres
			$genres_ids_string = "";
			$comma = "";
			// Generate values string
			foreach($genres_ids as $genreId){
				$genres_ids_string = $genres_ids_string . $comma . "(" . $genreId . ", " . $tbRp["leftId"] . ")";
				$comma = ", ";
			}
			$conn->query("INSERT INTO `".$tbRp['middle']['name']."` (".$tbRp['middle']['rightId'].", ".$tbRp['middle']['leftId'].") VALUES $genres_ids_string");
			return $conn->affected_rows > 0;
		}
	}

	function stringFix(&$strint){
		$strint = str_replace("'", "\'", $strint);
		$strint = str_replace('"', '\"', $strint);		
	}

	if (isset($_POST['done'])){
		$audio = $_POST['audio'] == 'on' ? 1 : 0;  stringFix($audio);

		$name = $_POST['book_title']; stringFix($name);
		$name || $error = "Вкажіть назву книги";

		json_decode($_POST['author_json_selected']) || $error = "Вкажіть автора";

		json_decode($_POST['genre_json_selected']) || $error = "Вкажіть жанр";

		$year = $_POST['year']; stringFix($year);
		$year || $error = "Вкажіть рік випуску";

		$language = $_POST['language']; stringFix($language);
		$language || $error = "Вкажіть мову";

		$pages = $_POST['pages']; stringFix($pages);
		$pages || $error = "Вкажіть кількість сторінок / тривалість (аудіокнига)";

		$description = $_POST['description']; stringFix($description);
		$description || $error = "Додайте опис книги";


		// Check book image
		( $_FILES && $_FILES["img"]["error"] == UPLOAD_ERR_OK ) || $error = "Зображення обкладинки відсутнє або не сумісне";

		// Check book file
		( $_FILES && $_FILES["book"]["error"] == UPLOAD_ERR_OK ) || $error = "Відсутній або несумісний файл книги";


		if (!$error){
			// Save book cover image on the server =============
			$filename = $_FILES["img"]["name"];
			$i = 2;
			while (in_array($filename, scandir('book_cover_img/'))){
				$filename = "($i)".$_FILES["img"]["name"];
				$i += 1;
			}
			$path = 'book_cover_img/'.$filename;
			move_uploaded_file($_FILES["img"]["tmp_name"], $path);
			$imgurl = '/'.$path;			

			// Save book file on the server ===================
			$filename = $_FILES["book"]["name"];
			$i = 2;
			while (in_array($filename, scandir('books/'))){
				$filename = "($i)".$_FILES["book"]["name"];
				$i += 1;
			}
			$path = 'books/'.$filename;
			move_uploaded_file($_FILES["book"]["tmp_name"], $path);
			$url = '/'.$path;
			// ================================================

			// Add the book to database
			$sql = "INSERT INTO `books` (`name`, `author`, `date`, `language`, `pages`, `url`, `description`, `coverimg`, `audio`, `postAuthor`) VALUES ('$name', '$author', '$year', '$language', '$pages', '$url', '$description', '$imgurl', '$audio', '$loggedUserLogin') RETURNING *";
			$createdBook = $conn->query($sql)->fetch_assoc();

			// Add authors
			selectItems($_POST['author_json_selected'], [
				"right" => ['name' => 'authors', 'value' => 'authorName'], 
				"middle" => ['name' => 'books-authors', 'leftId' => 'bookId', 'rightId' => 'authorId'],
				"leftId" => $createdBook['id']
			]);

			// Add genres
			selectItems($_POST['genre_json_selected'], [
				"right" => ['name' => 'genres', 'value' => 'genreName'], 
				"middle" => ['name' => 'books-genres', 'leftId' => 'bookId', 'rightId' => 'genreId'],
				"leftId" => $createdBook['id']
			]);

			header("location: /kniga.php?id=".$createdBook['id']);
		}
	}
?>
<!DOCTYPE html>
<html lang="ua">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="/styles/main.css">
	<title>LibCloud</title>
</head>

<body>
	<!-- Header -->
	<? require($_SERVER['DOCUMENT_ROOT'].'/elements/header.php'); ?>

	<div class="golovna">
		<div class="addbook">
			<div class="innerAddbook">
				<br><br>
				<h1>Нова книга</h1>
				<h3 class="error_text_h3"><? echo $error; ?></h3>
				<form action="" class="bookAddForm" method="POST" enctype="multipart/form-data" onsubmit="
					this.genre_json_selected.value = JSON.stringify(getSelectManyData('add_genre_input'));
					this.author_json_selected.value = JSON.stringify(getSelectManyData('add_author_input'));

				">
					<br>

					<!-- Book name -->
					<input placeholder="Назва" title="Назва книги" required type="text" name="book_title" style="font-size: 2em; padding: 0;">

					<div class="bookAddFormBlock inputs">
						<!-- Book author -->
						<div class="select_many" id="add_author_input">
							<input type="text" value="asd" class="display_none" name="author_json_selected">
							<span class="select_many_text">Виберіть автора(ів):</span>

							<!-- Item example -->
							<span class="select_many_item display_none">
								<span class="text" style="min-width: 1em;">Book author</span>
								<i class="fa-solid fa-xmark genre_cancel_btn" onclick="
									const selectManyElem = this.closest('.select_many');
									this.parentNode.remove(); 
									if (!selectManyElem.querySelector('.select_many_item:not(.display_none)')) selectManyElem.querySelector('select').value = ''; // Reset value if no options selected
								"></i>
							</span>

							<select required name="author" onchange="selectMany('add_author_input');">
								<option value="" disabled selected>+</option>
								<option value="-1" disabled class="display_none">+</option>
								<option value="new" placeholder="Ім'я автора">Новий автор</option>
								<?
								$sql = $conn->query("SELECT * FROM authors ORDER BY authorName");
								while ($author = $sql->fetch_assoc() ): ?>
									<option value="<? echo $author['id'] ?>"><? echo $author['authorName'] ?></option>
								<? endwhile; ?>
							</select>
						</div>

						<!-- Book release date -->
						<input placeholder="Рік" title="Рік випуску" required type="number" name="year">
						<div class="book-add-double-item">
							<!-- Audio or not -->
							<div title="Аудіокнига">
								<label for="is_book_audio"><i class="fa-solid fa-headphones"></i></label>
								<input type="checkbox" id="is_book_audio" name="audio" onchange="
									const nearInput = this.parentNode.nextElementSibling;
									nearInput.setAttribute('placeholder', this.checked ? 'Тривалість (хвилин)' : 'Кількість сторінок');
									nearInput.value = '';
									const input = document.getElementById('book_file_input');
									input.value = '';
									setImagePreview(input.closest('.file_selector'));
									input.setAttribute('accept', this.checked ? 'audio/*' : nonAudioBooksFormats);">
							</div>
							<!-- Pages count -->
							<input placeholder="Число сторінок" style="width: 70%;" required type="number" name="pages" title="Кількість сторінок \ тривалість (хвилин)">
							
						</div>
						
						<!-- Book language -->
						<input placeholder="Мова" title="Мова" required type="text" name="language">
						<!-- Genre -->
						<div class="select_many" id="add_genre_input">
							<input type="text" value="asd" class="display_none" name="genre_json_selected">
							<span class="select_many_text">Виберіть жанр(и):</span>

							<!-- Item example -->
							<span class="select_many_item display_none">
								<span class="text" style="min-width: 1em;">Genre</span>
								<i class="fa-solid fa-xmark genre_cancel_btn" onclick="
									const selectManyElem = this.closest('.select_many');
									this.parentNode.remove(); 
									if (!selectManyElem.querySelector('.select_many_item:not(.display_none)')) selectManyElem.querySelector('select').value = ''; // Reset value if no options selected
								"></i>
							</span>

							<select required name="genre" onchange="selectMany('add_genre_input');">
								<option value="" disabled selected>+</option>
								<option value="-1" disabled class="display_none">+</option>
								<option value="new" placeholder="Назва жанру">Новий жанр</option>
								<?
								$sql = $conn->query("SELECT * FROM genres ORDER BY genreName");
								while ($genre = $sql->fetch_assoc() ): ?>
									<option value="<? echo $genre['id'] ?>"><? echo $genre['genreName'] ?></option>
								<? endwhile; ?>
							</select>
						</div>
						
					</div>

					<div class="bookAddFormBlock add-files">
						<div class="file_inputs_block">
							<!-- Book cover image -->
							<label for="book_cover_img_input" class="file_selector">
								<img src="img/empty_book.png" alt="Book title">
								<span class="file_selector_text"><i class="fa-solid fa-plus"></i> Вибрати</span>
								<input type="file" id="book_cover_img_input" name="img" align="center" accept="image/*" required onchange="setImagePreview(this.parentNode, 'image');">
								<span class="file_selector_note">Зображення обкладинки</span>
								<span class="file_selector_note selected_file"></span>
							</label>


							<!-- Book file -->
							<label for="book_file_input" class="file_selector">
								<img src="img/file_image.png" alt="Book title">
								<span class="file_selector_text"><i class="fa-solid fa-plus"></i> Вибрати</span>
								<input  type="file" id="book_file_input" name="book" align="center" required onchange="setImagePreview(this.parentNode, 'file');" accept=".pdf,.epub,.azw,.html,.txt,.pdb,.prc,.doc,.docx,.drm">
								<span class="file_selector_note">Файл книги</span>
								<span class="file_selector_note selected_file"></span>
							</label>	
						</div>
					</div>


					<textarea placeholder="Опис" required rows="10" cols="50" name="description"></textarea><br>

					<button name="done" style="padding: 8px 20px; font-size: 1.7em; margin: 8px 0;">Створити</button>
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">

const nonAudioBooksFormats = ".pdf,.epub,.azw,.html,.txt,.pdb,.prc,.doc,.docx,.drm";

function setImagePreview(fileSelectorElement, type){
	const input = fileSelectorElement.querySelector('input');
	const [file] = input.files;
	const imgElem = fileSelectorElement.querySelector('img');

	if (file){
		fileSelectorElement.querySelector(".file_selector_text").innerHTML = "<i class='fa-solid fa-repeat'></i> Змінити";
		fileSelectorElement.setAttribute('fileselected', true);
		fileSelectorElement.querySelector(".selected_file").innerHTML = "" + stringShortify(file.name, 4, 7, 20, "<b style='color: white'>...</b>");
	} else {
		fileSelectorElement.querySelector(".file_selector_text").innerHTML = "<i class='fa-solid fa-plus'></i> Вибрати";
		fileSelectorElement.setAttribute('fileselected', false);
		fileSelectorElement.querySelector(".selected_file").innerHTML = "";		
	}
	if (type == 'image'){
		const placeholder = imgElem.getAttribute('placeholder');

		if (file) {
			// Save default image src, if it's not setted
			placeholder || imgElem.setAttribute('placeholder', imgElem.getAttribute('src'));
			// Set selected image src
			imgElem.src = URL.createObjectURL(file);
		} else {
			placeholder && ( imgElem.src = placeholder );
		}
	}
}

function stringShortify(string, start = 8, end = 8, maxLength = 16, separator = "..."){
	if (string.length < maxLength) return string;

	return string.slice(0, start) + separator + string.slice(-end);
}

function selectText(node) {
    if (document.body.createTextRange) {
        const range = document.body.createTextRange();
        range.moveToElementText(node);
        range.select();
    } else if (window.getSelection) {
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(node);
        selection.removeAllRanges();
        selection.addRange(range);
    } else {
        console.warn("Could not select text in node: Unsupported browser.");
    }
}

function selectMany(selectElemId){
	const elem = document.getElementById(selectElemId);
	const input = elem.querySelector('select');
	const selectedValueText = input.options[input.selectedIndex].text;
	const genresList = elem.querySelector('.select_many_selected_items');

	const genreItem = elem.querySelector('.select_many_item').cloneNode(true);
	genreItem.className = genreItem.className.replace('display_none', '');
	genreItemText = genreItem.querySelector('.text');
	genreItemText.innerHTML = selectedValueText;
	genreItem.setAttribute('value', input.value); // Id

	// Add genre item
	if (!elem.querySelector(`.select_many_item:not([value='new'])[value='${input.value}']`)){
		input.before(genreItem);
	}

	// If new genre
	if (input.value === 'new'){
		genreItem.setAttribute('new', ''); // Mark item as "new"
		genreItemText.innerHTML = input.options[input.selectedIndex].getAttribute('placeholder'); // Set placeholder
		genreItemText.setAttribute('contenteditable', ''); // Set contenteditable
		genreItemText.focus();
		selectText(genreItemText);
	}

	// Reset input
	input.value = '-1';
}

function getSelectManyData(selectElemId) {
	const elem = document.getElementById(selectElemId);

	const options = elem.querySelectorAll(".select_many_item:not(.select_many_item.display_none)");
	const values = {};
	for (let option of options){
		values[option.getAttribute('value')] = option.querySelector('.text').innerHTML;
	}
	return values;
}

	</script>

	<!-- Footer -->
	<? require("elements/footer.php"); ?>
</body>


</html>