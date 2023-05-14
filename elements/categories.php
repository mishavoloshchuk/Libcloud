<div class="janr">
	<? 
	$page = basename($_SERVER['PHP_SELF']);?>
	<div class="block" <? echo !$_GET['genre'] && (!$page || $page == "index.php") ? 'activ' : ''?>><a href='/'>Всі книги</a></div>
	<?php 
		$categories = $conn->query("SELECT * FROM `genres` ORDER BY genreName");
		while ($catg = $categories->fetch_assoc()){
			?>
			<div class="block" <? echo $_GET['genre'] == $catg['id'] ? 'activ' : ''?>><a href='/?genre=<? echo($catg["id"]);?>'><? echo($catg["genreName"]);?></a></div>
			<?php
		}
	?>
</div>