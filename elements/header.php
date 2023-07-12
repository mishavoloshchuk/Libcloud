<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php'); ?>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<script src="https://kit.fontawesome.com/e2076dc419.js" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/main.js"></script>

<div class="header_space"></div>
<header>
	<nav>
		<? $page = basename($_SERVER['PHP_SELF']); ?>
		<div class="logo"><a href="/">Lib<span class="logoExtended">Cloud</span><i class="fa-solid fa-cloud"></i></a></div>
		<form class="search" method="GET" action="/"><input type="text" placeholder="Пошук" name="search"><button class="seach_icon" type="submit" title="Шукати"><i class="fa-solid fa-magnifying-glass"></button></i></form>
		<nav class="nav_menu desktop_nav_menu">
			<!-- <a class="counter-icon nav_item">
				<i class="fa-solid fa-bag-shopping"></i>
				<span class="counter">6</span>
			</a> -->
			<? $status = $loggedUser['status'];

			if ($loggedUserLogin): ?>
				<?php if ($status == 'Admin' && $page != 'addbook.php'): ?>
					<!-- Add book -->
					<div class="nav_item"><a href="/addbook.php"><i class="fa-solid fa-plus"></i> Додати книгу</a></div>
				<?php endif; ?>

				<? if ($page == 'profile.php'): ?>
					<!-- Logout -->
					<div class="nav_item"><a href="logout.php">Вихід <i class="fa-solid fa-right-from-bracket"></i></a></div>
				<? else: ?>
					<!-- User login -->
					<div class="nav_item"><a href="/profile.php"><i class="fa-solid fa-user"></i> <?php echo $loggedUserLogin; if ($status!='User'){echo " ($status)";}?></a></div>
				<? endif; ?>
			<?php else: ?>
				<? if ($page != 'register.php'): ?>
					<div class="nav_item"><a href="/register.php"><i class="fa-solid fa-user-plus"></i> Реєстрація</a></div>
				<? endif; if ($page != 'auth.php'): ?>
					<div class="nav_item"><a href="/auth.php"><i class="fa-solid fa-right-to-bracket"></i> Авторизація</a></div>
				<? endif; ?>
			<?php endif; ?>
		</nav>
		<!-- Mobile menu -->
		<span class="mobile_nav_menu" onclick="showSideMenu('mobile_nav_side_menu');"><i class="fa-solid fa-bars"></i></span>
	</nav>
</header>

<section class="side-menu-wrapper" id="mobile_nav_side_menu" animationDuration="400" animstate="hidden">
	<div class="side-menu">
		<div class="close-button" onclick="hideSideMenu(this.parentNode.parentNode.id);"><i class="fa-solid fa-xmark"></i></div>
		<div class="inner-side-menu">
			<section>
				<div class="logo logo_inside_menu"><a href="/">LibCloud<i class="fa-solid fa-cloud"></i></a></div>
				<!-- <h2 class="side-menu-title">Меню</h2> -->
				<?php
				if ($loggedUserLogin): ?>
					<?php if ($status == 'Admin' && $page != 'addbook.php'): ?>
						<!-- Add book -->
						<div class="side-menu-item"><a class="menu_nav_link" href="/addbook.php"><i class="fa-solid fa-plus"></i>Додати книгу</a></div>
					<? endif;
					$profile_menu = $page == "profile.php" ? $_GET['menu'] : "false";
					if ($status == 'Admin' && $profile_menu != 'Додані книги'): ?>
						<div class="side-menu-item" <? echo $menu == 'Додані книги' ? 'activ' : ''?>><a class="menu_nav_link" href='profile.php?menu=Додані книги'><i class="fa-solid fa-book"></i>Додані книги</a></div>
					<? endif; if ($profile_menu != 'Зараз читаю'): ?>
						<div class="side-menu-item" <? echo $menu == 'Зараз читаю' ? 'activ' : ''?>><a class="menu_nav_link" href='profile.php?menu=Зараз читаю'><i class="fa-solid fa-book-open"></i>Зараз читаю</a></div>
					<? endif; if ($profile_menu != 'Прочитані книги'): ?>
						<div class="side-menu-item" <? echo $menu == 'Прочитані книги' ? 'activ' : ''?>><a class="menu_nav_link" href='profile.php?menu=Прочитані книги'><i class="fa-solid fa-check"></i>Прочитані книги</a></div>
					<? endif; if ($profile_menu != 'Історія переглянутих книг'): ?>
						<div class="side-menu-item" <? echo $menu == 'Історія переглянутих книг' ? 'activ' : ''?>><a class="menu_nav_link" href='profile.php?menu=Історія переглянутих книг'><i class="fa-solid fa-clock-rotate-left"></i>Історія переглянутих книг</a></div>
					<? endif; if ($profile_menu != 'Мої колекції'): ?>
						<div class="side-menu-item" <? echo $menu == 'Мої колекції' ? 'activ' : ''?>><a class="menu_nav_link" href='profile.php?menu=Мої колекції'><i class="fa-solid fa-layer-group"></i>Мої колекції</a></div>
					<? endif; ?>
					
					<!-- Logout -->
					<div class="side-menu-item"><a class="menu_nav_link" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Вихід | <?php echo $loggedUserLogin; if ($status!='User'){echo " ($status)";};?></a></div>
					
				<?php else: ?>
					<? if ($page != 'register.php'): ?>
						<div class="side-menu-item"><a href="/register.php"><i class="fa-solid fa-user-plus"></i> Реєстрація</a></div>
					<? endif; if ($page != 'auth.php'): ?>
						<div class="side-menu-item"><a href="auth.php"><i class="fa-solid fa-right-to-bracket"></i> Авторизація</a></div>
					<? endif; ?>
				<?php endif; ?>
			</section>
			<section class="mobile_side_menu_catogories">
				<h2 class="side-menu-title">Категорії</h2>
				<!-- Categories -->
				<? require('elements/categories.php'); ?>
			</section>
		</div>
	</div>
	<div class="tint" onclick="hideSideMenu(this.parentNode.id);"></div>
</section>
<script type="text/javascript">
	refresh();
	function refresh(){
		requestAnimationFrame(refresh);

		document.querySelector('header').setAttribute('isscroll', document.documentElement.scrollTop > 15);
	}
</script>