<!doctype html>
<html lang="ru">
<head>
	
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<?php echo flixcdn_css(array(
		'/flixcdn/admin/assets/css/bootstrap.min.css?v=3',
		'/flixcdn/admin/assets/css/jquery-ui.min.css',
		'/flixcdn/admin/assets/css/chosen.css',
		'/flixcdn/admin/assets/css/app.css?v=5',
	)); ?>

	<?php echo flixcdn_js('/flixcdn/admin/assets/js/fontawesome.min.js'); ?>

	<title><?php echo $pageTitle; ?></title>

</head>
<body class="vh">

	<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
	  <div class="container-fluid">
	    <a class="navbar-brand" href="<?php echo $baseUrl; ?>">FlixCDN</a>
	    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
	      <span class="navbar-toggler-icon"></span>
	    </button>

	    <div class="collapse navbar-collapse" id="navbarColor01">
	      <ul class="navbar-nav me-auto">
	        <li class="nav-item">
						<a class="nav-link<?php echo (stripos($action, 'settings') !== false ? ' active' : ''); ?>" href="<?php echo flixcdn_action('settings'); ?>">
							Настройки модуля
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link<?php echo (stripos($action, 'replacement') !== false ? ' active' : ''); ?>" href="<?php echo flixcdn_action('replacement'); ?>">
							Массовое проставление данных
						</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link<?php echo (stripos($action, 'base') !== false ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>">Мониторинг новинок</a>
						<!-- <a class="nav-link dropdown-toggle<?php echo (stripos($action, 'base') !== false ? ' active' : ''); ?>" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Мониторинг новинок</a>
						<div class="dropdown-menu" style="">
							<a class="dropdown-item" href="<?php echo flixcdn_action('base'); ?>">Поиск по базе</a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item disabled" href="#">Фильмы</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && !$cat && !$search ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies">Все</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && $cat == 2 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies&cat=2">Русские</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && $cat == 3 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies&cat=3">Зарубежные</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && $cat == 4 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies&cat=4">Аниме</a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item disabled" href="#">Сериалы</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && !$cat ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials">Все</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && $cat == 2 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials&cat=2">Русские</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && $cat == 3 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials&cat=3">Зарубежные</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && $cat == 4 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials&cat=4">Аниме</a>
						</div> -->
					</li>
	      </ul>
	      <form class="d-flex">
	        <a href="<?php echo $PHP_SELF; ?>" class="btn btn-secondary" target="_blank">Админпанель сайта</a>
	      </form>
	    </div>
	  </div>
	</nav>

	<div class="container">

		<?php if (!empty($flixcdnUpdateAvailable)): ?>
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<strong>Доступно обновление!</strong> Вышла новая версия FlixCDN <?php echo htmlspecialchars($flixcdnUpdateAvailable); ?>. Текущая версия: <?php echo htmlspecialchars($flixcdn->version()); ?>.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>