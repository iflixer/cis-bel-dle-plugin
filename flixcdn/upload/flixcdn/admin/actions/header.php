<!doctype html>
<html lang="uk">
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
							Налаштування модуля
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link<?php echo (stripos($action, 'replacement') !== false ? ' active' : ''); ?>" href="<?php echo flixcdn_action('replacement'); ?>">
							Масове проставлення даних
						</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link<?php echo (stripos($action, 'base') !== false ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>">Моніторинг новинок</a>
						<!-- <a class="nav-link dropdown-toggle<?php echo (stripos($action, 'base') !== false ? ' active' : ''); ?>" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Моніторинг новинок</a>
						<div class="dropdown-menu" style="">
							<a class="dropdown-item" href="<?php echo flixcdn_action('base'); ?>">Пошук по базі</a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item disabled" href="#">Фільми</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && !$cat && !$search ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies">Усі</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && $cat == 2 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies&cat=2">Російські</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && $cat == 3 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies&cat=3">Зарубіжні</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'movies' && $cat == 4 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=movies&cat=4">Аніме</a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item disabled" href="#">Серіали</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && !$cat ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials">Усі</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && $cat == 2 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials&cat=2">Російські</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && $cat == 3 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials&cat=3">Зарубіжні</a>
							<a class="dropdown-item<?php echo (stripos($action, 'base') !== false && $section == 'serials' && $cat == 4 ? ' active' : ''); ?>" href="<?php echo flixcdn_action('base'); ?>&section=serials&cat=4">Аніме</a>
						</div> -->
					</li>
	      </ul>
	      <form class="d-flex">
	        <a href="<?php echo $PHP_SELF; ?>" class="btn btn-secondary" target="_blank">Адмінпанель сайту</a>
	      </form>
	    </div>
	  </div>
	</nav>

	<div class="container">

		<?php if (!empty($flixcdnUpdateAvailable)): ?>
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<strong>Доступне оновлення!</strong> Вийшла нова версія FlixCDN <?php echo htmlspecialchars($flixcdnUpdateAvailable); ?>. Поточна версія: <?php echo htmlspecialchars($flixcdn->version()); ?>.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>