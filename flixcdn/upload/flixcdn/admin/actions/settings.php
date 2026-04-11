<?php

// Save

if (isset($_POST['settings'])) {

	// Cronkey

	if ($flixcdn->config['cronkey'])
		$cronkey = $flixcdn->config['cronkey'];

	// Domain

	if ($flixcdn->config['domain'])
		$domain = $flixcdn->config['domain'];

	if ($flixcdn->config['domain_update'])
		$domain_update = $flixcdn->config['domain_update'];

	$flixcdn->config = $_POST['settings'];

	// Custom Qualities

	if ($flixcdn->config['custom']['qualities']) {

		$custom_qualities = array();

		$data = explode("\r\n", $flixcdn->config['custom']['qualities']);

		if ($data) foreach ($data as $string) {
			list($pattern, $replacement) = explode('|', $string);

			if ($pattern)
				$custom_qualities[$pattern] = $replacement;
		}

		$flixcdn->config['custom']['qualities'] = $custom_qualities;

	} else
		$flixcdn->config['custom']['qualities'] = array();

	// Custom Translations

	if ($flixcdn->config['custom']['translations']) {

		$custom_translations = array();

		$data = explode("\r\n", $flixcdn->config['custom']['translations']);

		if ($data) foreach ($data as $string) {
			list($pattern, $replacement) = explode('|', $string);

			if ($pattern)
				$custom_translations[$pattern] = $replacement;
		}

		$flixcdn->config['custom']['translations'] = $custom_translations;

	} else
		$flixcdn->config['custom']['translations'] = array();

	if ($flixcdn->config['custom']['genres']) {

		$custom_genres = array();

		$data = explode("\r\n", $flixcdn->config['custom']['genres']);

		if ($data) foreach ($data as $string) {
			$parts = explode('|', $string);
			
			if (count($parts) >= 2 && $parts[0]) {
				$genre = trim($parts[0]);
				$category = trim($parts[1]);
				
				if ($flixcdn->config['genres_storage'] === 'categories') {
					$custom_genres[$genre] = intval($category);
				} else {
					$custom_genres[$genre] = $category;
				}
			}
		}

		$flixcdn->config['custom']['genres'] = $custom_genres;

	} else
		$flixcdn->config['custom']['genres'] = array();

	// Translations

	$flixcdnApi = new FlixCDNApi($flixcdn->config['api']);

	$translations = $flixcdnApi->getTranslations();

	$genresApi = $flixcdnApi->getGenres();

	// Save

	if ($translations)
		$flixcdn->config['translations'] = $translations;

	if ($genresApi)
		$flixcdn->config['genres'] = $genresApi;

	if ($cronkey)
		$flixcdn->config['cronkey'] = $cronkey;

	if ($domain)
		$flixcdn->config['domain'] = $domain;

	if ($domain_update)
		$flixcdn->config['domain_update'] = $domain_update;

	if ($flixcdn->config['update']['serials']['priority'])
		$flixcdn->config['update']['serials']['priority'] = explode(',', $flixcdn->config['update']['serials']['priority']);

	$fh = fopen(FLIXCDN_DIR . '/config.php', 'w');
	fwrite($fh, '<?php' . "\r\n\r\nreturn " . var_export($flixcdn->config, true) . ';');
	fclose($fh);

	echo json_encode(array('status' => 'success'));
	exit;

}

// Translations

/*if ($flixcdn->config['api']['token']) {

	$flixcdnApi = new FlixCDNApi($flixcdn->config['api']);

	$data = $flixcdnApi->getTranslations();

	if ($data && !$data['code']) {
		$translations = array();

		foreach ($data as $translation)
			$translations[intval($translation['id'])] = $translation['name'];

		if ($translations) {
			$flixcdn->config['translations'] = $translations;

			$fh = fopen(FLIXCDN_DIR . '/config.php', 'w');
			fwrite($fh, '<?php' . "\r\n\r\nreturn " . var_export($flixcdn->config, true) . ';');
			fclose($fh);
		}
	}

}*/

// Xfields

$xfields = array('' => '');

$xfieldsload = xfieldsload();

if ($xfieldsload)
	foreach ($xfieldsload as $key => $value) {
		if (in_array($value[3], array('text', 'textarea', 'select')))
			$xfields[$value[0]] = $value[1];
	}

// Not Update Xfields

$not_update_xfields = array('' => '');

if ($xfieldsload)
	foreach ($xfieldsload as $key => $value) {
		if (in_array($value[3], array('yesorno')))
			$not_update_xfields[$value[0]] = $value[1];
	}

// Cronkey

if (!$flixcdn->config['cronkey']) {

	$cronkey = md5($config['http_home_url'] . time());

	$flixcdn->config['cronkey'] = $cronkey;

	$fh = fopen(FLIXCDN_DIR . '/config.php', 'w');
	fwrite($fh, '<?php' . "\r\n\r\nreturn " . var_export($flixcdn->config, true) . ';');
	fclose($fh);

}

$cron = "{$config['http_home_url']}flixcdn.php?key={$flixcdn->config['cronkey']}";

// Qualities

$flixcdnUpdate = new FlixCDNUpdate($flixcdn->config);

$qualities = array();

foreach ($flixcdnUpdate->quality as $quality)
	$qualities[] = $quality;

// Qualities

$translations = array();

if ($flixcdn->config['translations']) foreach ($flixcdn->config['translations'] as $translation)
	$translations[] = $translation;

$genres = array();

if ($flixcdn->config['genres']) {
	foreach ($flixcdn->config['genres'] as $genre) {
		if (is_array($genre) && isset($genre['name'])) {
			$genres[] = array(
				'value' => $genre['name'],
				'text' => $genre['name'],
				'id' => $genre['id'] ?? null
			);
		} else {
			$genres[] = array(
				'value' => $genre,
				'text' => $genre,
				'id' => null
			);
		}
	}
	
	usort($genres, function($a, $b) {
		return strcmp($a['text'], $b['text']);
	});
}

$categories = array();

global $cat_info;

if (!empty($cat_info) && is_array($cat_info)) {
	foreach ($cat_info as $cat_id => $cat_data) {
		$categories[] = array(
			'value' => $cat_id,
			'text' => $cat_data['name']
		);
	}
	
	usort($categories, function($a, $b) {
		return strcmp($a['text'], $b['text']);
	});
}

// Settings

$pageTitle = 'FlixCDN - Налаштування модуля';

include dirname(__FILE__) . '/header.php';

?>

<form id="settingsForm" action="<?php echo flixcdn_action('settings'); ?>" method="post">

	<div class="accordion mb-2" id="accordionSettings">

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingOther">
	      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOther" aria-expanded="true" aria-controls="collapseOther">
	        Загальні налаштування
	      </button>
	    </h2>

			<div id="collapseOther" class="accordion-collapse collapse show" aria-labelledby="headingOther" data-bs-parent="#accordionSettings" style="">
      	<div class="accordion-body">
				
					<div class="row">
								
						<?php echo FlixCDNForm::group(
							'moduleOn',
							'Модуль',
							FlixCDNForm::_switch(
								'moduleOn',
								'settings[on]',
								$flixcdn->config['on'] ? true : false
							),
							'Увімкнення та вимкнення роботи модуля'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingApi">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApi" aria-expanded="false" aria-controls="collapseApi">
	        Налаштування доступу до API
	      </button>
	    </h2>

			<div id="collapseApi" class="accordion-collapse collapse" aria-labelledby="headingApi" data-bs-parent="#accordionSettings" style="">
      	<div class="accordion-body">
				
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleApiToken',
							'API Ключ',
							FlixCDNForm::text(
								'moduleApiToken',
								'settings[api][token]',
								$flixcdn->config['api']['token'] ? $flixcdn->config['api']['token'] : false,
								'API Ключ'
							),
							'Ваш персональний API Ключ'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleApiDomain',
							'API Домен',
							FlixCDNForm::text(
								'moduleApiDomain',
								'settings[api][domain]',
								$flixcdn->config['api']['domain'] ? $flixcdn->config['api']['domain'] : false,
								'http://example.com/'
							),
							'Домен для доступу до API (не обов\'язково)'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingPlayer">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlayer" aria-expanded="false" aria-controls="collapsePlayer">
	        Налаштування виводу плеєра
	      </button>
	    </h2>

			<div id="collapsePlayer" class="accordion-collapse collapse" aria-labelledby="headingPlayer" data-bs-parent="#accordionSettings" style="">
      	<!-- <div class="alert alert-dismissible alert-primary mb-0" style="margin:5px;border-radius:3px"> -->
      	<div class="alert alert-dismissible alert-primary" style="margin:5px;border-radius:3px">
				
					<div>
						<strong>[flixcdn-found]{flixcdn-player}[/flixcdn-found]</strong>
						&mdash; Вивід плеєра в шаблоні повної новини (<strong>fullstory.tpl</strong>)
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[flixcdn-notfound] ... [/flixcdn-notfound]</strong>
						&mdash; Код у цих тегах буде виведений якщо посилання на джерело плеєра не заповнене (<strong>fullstory.tpl</strong>)
					</div>

				</div>

      	<div class="accordion-body" style="display:none">

					<div class="row">

						<?php /*echo FlixCDNForm::group(
							'modulePlayerD',
							'Основной домен сайта',
							FlixCDNForm::text(
								'modulePlayerD',
								'settings[d]',
								$flixcdn->config['d'] ? $flixcdn->config['d'] : false,
								'example.com'
							),
							'Основной домен/зеркало вашего сайта<br>(обязательно указывать для корректного вывода статистики в личном кабинете веб-мастера)'
						);*/ ?>

						<?php /*echo FlixCDNForm::group(
							'modulePlyerScript',
							'JS Скрипт',
							FlixCDNForm::text(
								'modulePlyerScript',
								'settings[player][script]',
								$flixcdn->config['player']['script'] ? $flixcdn->config['player']['script'] : false,
								'https://example.com/script.js'
							),
							'Скрипт для замены не рабочего домена плеера'
						);*/ ?>

						<?php /*echo FlixCDNForm::group(
							'modulePlayerParams',
							'Глобальные параметры плеера',
							FlixCDNForm::text(
								'modulePlayerParams',
								'settings[player][params]',
								$flixcdn->config['player']['params'] ? $flixcdn->config['player']['params'] : false,
								'param1=value1&amp;param2=value2'
							),
							'Глобальные параметры плеера<br><br>(параметры в этом поле доступны для всех плееров выводимых на сайте)'
						);*/ ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingXfields">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseXfields" aria-expanded="false" aria-controls="collapseXfields">
	        Налаштування дод. полів
	      </button>
	    </h2>

			<div id="collapseXfields" class="accordion-collapse collapse" aria-labelledby="headingXfields" data-bs-parent="#accordionSettings" style="">
      	<div class="accordion-body">
				
					<h4 class="card-header sub-card-header mb-3">Обов'язкові поля для роботи модуля</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsSearchKinopoisk',
							'Kinopoisk ID',
							FlixCDNForm::select(
								'moduleXfieldsSearchKinopoisk',
								'settings[xfields][search][kinopoisk_id]',
								$xfields,
								$flixcdn->config['xfields']['search']['kinopoisk_id']
							),
							'Дод. поле для пошуку за Kinopoisk ID'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsSearchImdb',
							'Imdb ID',
							FlixCDNForm::select(
								'moduleXfieldsSearchImdb',
								'settings[xfields][search][imdb_id]',
								$xfields,
								$flixcdn->config['xfields']['search']['imdb_id']
							),
							'Дод. поле для пошуку за Imdb ID'
						); ?>

					</div>

					<hr class="vh-separator">

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteIframe',
							'Джерело відео',
							FlixCDNForm::select(
								'moduleXfieldsWriteIframe',
								'settings[xfields][write][source]',
								$xfields,
								$flixcdn->config['xfields']['write']['source']
							),
							'Дод. поле для заповнення джерела відео (посилання для виводу плеєра)'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteQuality',
							'Якість відео',
							FlixCDNForm::select(
								'moduleXfieldsWriteQuality',
								'settings[xfields][write][quality]',
								$xfields,
								$flixcdn->config['xfields']['write']['quality']
							),
							'Дод. поле для заповнення якості відео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTranslation',
							'Переклад',
							FlixCDNForm::select(
								'moduleXfieldsWriteTranslation',
								'settings[xfields][write][translation]',
								$xfields,
								$flixcdn->config['xfields']['write']['translation']
							),
							'Дод. поле для заповнення перекладу відео'
						); ?>

					</div>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTranslations',
							'Список перекладів серіалу',
							FlixCDNForm::select(
								'moduleXfieldsWriteTranslations',
								'settings[xfields][write][translations]',
								$xfields,
								$flixcdn->config['xfields']['write']['translations']
							),
							'Дод. поле для заповнення списку всіх перекладів серіалу'
						); ?>

					</div>

					<hr class="vh-separator">

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteSeason',
							'Номер останнього сезону',
							FlixCDNForm::select(
								'moduleXfieldsWriteSeason',
								'settings[xfields][write][season]',
								$xfields,
								$flixcdn->config['xfields']['write']['season']
							),
							'Дод. поле для заповнення номера останнього сезону серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteEpisode',
							'Номер останньої серії',
							FlixCDNForm::select(
								'moduleXfieldsWriteEpisode',
								'settings[xfields][write][episode]',
								$xfields,
								$flixcdn->config['xfields']['write']['episode']
							),
							'Дод. поле для заповнення номера останньої серії серіалу'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Альтернативний вивід даних</h4>
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomQualitySet',
							'Список своїх назв для якості відео',
							'<div>
								<button id="customQualityButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customQualityModal">Налаштувати свої назви якостей</button>
							</div>',
							'Налаштування своїх назв для якості відео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomQuality',
							'Якість відео (із заміною)',
							FlixCDNForm::select(
								'moduleXfieldsWriteCustomQuality',
								'settings[xfields][write][custom_quality]',
								$xfields,
								$flixcdn->config['xfields']['write']['custom_quality']
							),
							'Дод. поле для заповнення якості відео із заміною назв'
						); ?>

					</div>

					<hr class="vh-separator">

					<h4 class="card-header sub-card-header mb-3">Налаштування жанрів</h4>
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'genresStorageMode',
							'Спосіб зберігання жанрів',
							FlixCDNForm::radio(
								'genresStorageModeXfields',
								'settings[genres_storage]',
								'У дод. поля (як текст)',
								'xfields',
								$flixcdn->config['genres_storage'] ?? 'xfields'
							) . '<div class="text-muted mb-2">Жанри будуть зберігатися як текст у вказане додаткове поле</div>' . FlixCDNForm::radio(
								'genresStorageModeCategories',
								'settings[genres_storage]',
								'У категорії DLE',
								'categories',
								$flixcdn->config['genres_storage'] ?? 'xfields'
							) . '<div class="text-muted mb-2">Жанри будуть прив'язуватися до існуючих категорій DLE</div>',
							'Оберіть спосіб зберігання жанрів у системі'
						); ?>

						<div class="col-12" id="genresXfieldSelection" style="display: <?php echo ($flixcdn->config['genres_storage'] ?? 'xfields') === 'xfields' ? 'block' : 'none'; ?>;">
							<?php echo FlixCDNForm::group(
								'moduleXfieldsWriteGenresCustom',
								'Дод. поле для жанрів',
								FlixCDNForm::select(
									'moduleXfieldsWriteGenresCustom',
									'settings[xfields][write][genres_custom]',
									$xfields,
									$flixcdn->config['xfields']['write']['genres_custom'] ?? ''
								),
								'Дод. поле для збереження жанрів (при виборі режиму "У дод. поля")'
							); ?>
						</div>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomGenresSet',
							'Мапінг жанрів',
							'<div>
								<button id="customGenresButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customGenresModal">Налаштувати відповідність жанрів</button>
							</div>',
							'Налаштування відповідності жанрів з API до категорій або назв'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomTranslationSet',
							'Список своїх назв для перекладів',
							'<div>
								<button id="customTranslationButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customTranslationModal">Налаштувати свої назви перекладів</button>
							</div>',
							'Налаштування своїх назв для перекладів відео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomTranslation',
							'Переклад (із заміною)',
							FlixCDNForm::select(
								'moduleXfieldsWriteCustomTranslation',
								'settings[xfields][write][custom_translation]',
								$xfields,
								$flixcdn->config['xfields']['write']['custom_translation']
							),
							'Дод. поле для заповнення перекладу відео із заміною назв'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomTranslations',
							'Список перекладів серіалу (із заміною)',
							FlixCDNForm::select(
								'moduleXfieldsWriteCustomTranslations',
								'settings[xfields][write][custom_translations]',
								$xfields,
								$flixcdn->config['xfields']['write']['custom_translations']
							),
							'Дод. поле для заповнення списку всіх перекладів серіалу із заміною назв'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatSeasonType',
							'Тип форматування сезону',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatSeasonType',
								'settings[xfields][write][format_season_type]',
								array(
									0 => '',
									1 => '1 сезон, 2 сезон, 3 сезон',
									2 => '1 сезон, 1-2 сезон, 1-3 сезон',
									3 => '1 сезон, 1,2 сезон, 1,2,3 сезон'
								),
								$flixcdn->config['xfields']['write']['format_season_type']
							),
							'Тип форматування сезону серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatSeason',
							'Форматований сезон',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatSeason',
								'settings[xfields][write][format_season]',
								$xfields,
								$flixcdn->config['xfields']['write']['format_season']
							),
							'Дод. поле для заповнення форматованого сезону серіалу'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatEpisodeType',
							'Тип форматування серії',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatEpisodeType',
								'settings[xfields][write][format_episode_type]',
								array(
									0 => '',
									1 => '1 серія, 2 серія, 3 серія',
									2 => '1 серія, 1-2 серія, 1-3 серія, 1-4 серія',
									3 => '1 серія, 1,2 серія, 1,2,3 серія, 1,2,3,4 серія',
									4 => '1 серія, 1,2 серія, 1,2,3 серія, 2,3,4 серія',
									5 => '1,2 серія, 1,2,3 серія, 1,2,3 серія, 1,2,3,4,5 серія, 1-5,6,7 серія'
								),
								$flixcdn->config['xfields']['write']['format_episode_type']
							),
							'Тип форматування серії серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatEpisode',
							'Форматована серія',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatEpisode',
								'settings[xfields][write][format_episode]',
								$xfields,
								$flixcdn->config['xfields']['write']['format_episode']
							),
							'Дод. поле для заповнення форматованої серії серіалу'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Додаткові поля для виводу даних</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTitleRu',
							'Назва Російською',
							FlixCDNForm::select(
								'moduleXfieldsWriteTitleRu',
								'settings[xfields][write][title_rus]',
								$xfields,
								$flixcdn->config['xfields']['write']['title_rus']
							),
							'Дод. поле для заповнення назви фільму або серіалу Російською мовою'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTitleOrig',
							'Оригінальна назва',
							FlixCDNForm::select(
								'moduleXfieldsWriteTitleOrig',
								'settings[xfields][write][title_orig]',
								$xfields,
								$flixcdn->config['xfields']['write']['title_orig']
							),
							'Дод. поле для заповнення Оригінальної назви фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteSlogan',
							'Слоган',
							FlixCDNForm::select(
								'moduleXfieldsWriteSlogan',
								'settings[xfields][write][slogan]',
								$xfields,
								$flixcdn->config['xfields']['write']['slogan']
							),
							'Дод. поле для заповнення слогану фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteDescription',
							'Опис',
							FlixCDNForm::select(
								'moduleXfieldsWriteDescription',
								'settings[xfields][write][description]',
								$xfields,
								$flixcdn->config['xfields']['write']['description']
							),
							'Дод. поле для заповнення Опису фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteYear',
							'Рік випуску',
							FlixCDNForm::select(
								'moduleXfieldsWriteYear',
								'settings[xfields][write][year]',
								$xfields,
								$flixcdn->config['xfields']['write']['year']
							),
							'Дод. поле для заповнення Року випуску фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteDuration',
							'Тривалість відео',
							FlixCDNForm::select(
								'moduleXfieldsWriteDuration',
								'settings[xfields][write][duration]',
								$xfields,
								$flixcdn->config['xfields']['write']['duration']
							),
							'Дод. поле для заповнення Тривалості відео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteGenres',
							'Жанри',
							FlixCDNForm::select(
								'moduleXfieldsWriteGenres',
								'settings[xfields][write][genres]',
								$xfields,
								$flixcdn->config['xfields']['write']['genres']
							),
							'Дод. поле для заповнення списку Жанрів фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCountries',
							'Країни',
							FlixCDNForm::select(
								'moduleXfieldsWriteCountries',
								'settings[xfields][write][countries]',
								$xfields,
								$flixcdn->config['xfields']['write']['countries']
							),
							'Дод. поле для заповнення списку Країн фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteAge',
							'Вікове обмеження',
							FlixCDNForm::select(
								'moduleXfieldsWriteAge',
								'settings[xfields][write][age]',
								$xfields,
								$flixcdn->config['xfields']['write']['age']
							),
							'Дод. поле для заповнення Вікового обмеження фільму або серіалу'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWritePoster',
							'Постер',
							FlixCDNForm::select(
								'moduleXfieldsWritePoster',
								'settings[xfields][write][poster]',
								$xfields,
								$flixcdn->config['xfields']['write']['poster']
							),
							'Дод. поле для заповнення посилання на постер фільму або серіалу'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingSeo">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeo" aria-expanded="false" aria-controls="collapseSeo">
	        Шаблони СЕО даних
	      </button>
	    </h2>

			<div id="collapseSeo" class="accordion-collapse collapse" aria-labelledby="headingSeo" data-bs-parent="#accordionSettings" style="">
      	
				<div class="alert alert-dismissible alert-primary mb-0" style="margin:5px;border-radius:3px">
					
					<div>
						<strong>[movie] ... [/movie]</strong>
						&mdash; Текст у цих тегах буде використовуватися лише для фільмів
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[serial] ... [/serial]</strong>
						&mdash; Текст у цих тегах буде використовуватися лише для серіалів
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[year]{year}[/year]</strong>
						&mdash; Рік випуску фільму або серіалу
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[title_rus]{title_rus}[/title_rus]</strong>
						&mdash; Назва фільму або серіалу Російською мовою
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[title_orig]{title_orig}[/title_orig]</strong>
						&mdash; Оригінальна назва фільму або серіалу
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[quality]{quality}[/quality]</strong>
						&mdash; Якість відео фільму або серіалу
					</div>

					<hr class="vh-separator mt-2 mb-2">
					
					<div>
						<strong>[translation]{translation}[/translation]</strong>
						&mdash; Переклад фільму або серіалу
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[season]{season}[/season]</strong>
						&mdash; Номер останнього сезону серіалу
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[episode]{episode}[/episode]</strong>
						&mdash; Номер останньої серії серіалу
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[custom_quality]{custom_quality}[/custom_quality]</strong>
						&mdash; Якість відео фільму або серіалу<br>(із заміною на свої назви)
					</div>

					<hr class="vh-separator mt-2 mb-2">
					
					<div>
						<strong>[custom_translation]{custom_translation}[/custom_translation]</strong>
						&mdash; Переклад фільму або серіалу<br>(із заміною на свої назви)
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[format_season]{format_season}[/format_season]</strong>
						&mdash; Форматований вивід сезону серіалу<br>(тип форматування вказується в розділі "Налаштування полів для заповнення")
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[format_episode]{format_episode}[/format_episode]</strong>
						&mdash; Форматований вивід серії серіалу<br>(тип форматування вказується в розділі "Налаштування полів для заповнення")
					</div>

				</div>

				<div class="accordion-body">

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSeoOn',
							'Заповнення СЕО даних',
							FlixCDNForm::_switch(
								'moduleSeoOn',
								'settings[seo][on]',
								$flixcdn->config['seo']['on'] ? true : false
							),
							'Увімкнення та вимкнення заповнення СЕО даних'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSeoUrl',
							'ЧПУ новини',
							FlixCDNForm::text(
								'moduleSeoUrl',
								'settings[seo][url]',
								$flixcdn->config['seo']['url'] ? $flixcdn->config['seo']['url'] : false,
								''
							),
							'Шаблон заповнення ЧПУ новини (перекладається в транслітерацію)'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSeoTitle',
							'Заголовок новини',
							FlixCDNForm::text(
								'moduleSeoTitle',
								'settings[seo][title]',
								$flixcdn->config['seo']['title'] ? $flixcdn->config['seo']['title'] : false,
								''
							),
							'Шаблон заповнення заголовку новини'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSeoMetaTitle',
							'Мета-заголовок новини',
							FlixCDNForm::text(
								'moduleSeoMetaTitle',
								'settings[seo][meta][title]',
								$flixcdn->config['seo']['meta']['title'] ? $flixcdn->config['seo']['meta']['title'] : false,
								''
							),
							'Шаблон заповнення мета-заголовку новини'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSeoMetaDescription',
							'Мета-опис новини',
							FlixCDNForm::text(
								'moduleSeoMetaDescription',
								'settings[seo][meta][description]',
								$flixcdn->config['seo']['meta']['description'] ? $flixcdn->config['seo']['meta']['description'] : false,
								''
							),
							'Шаблон заповнення мета-опису новини'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingUpdate">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUpdate" aria-expanded="false" aria-controls="collapseUpdate">
	        Налаштування оновлення
	      </button>
	    </h2>

			<div id="collapseUpdate" class="accordion-collapse collapse" aria-labelledby="headingUpdate" data-bs-parent="#accordionSettings" style="">

				<div class="alert alert-dismissible alert-primary mb-0 cron-doc" style="margin:5px;border-radius:3px">
				
					<h4>Приклад налаштування <strong>crontab</strong> на сервері</h4>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>0 */3 * * *</strong> /usr/bin/wget --no-check-certificate -t 1 -O - '<strong><?php echo $cron; ?></strong>' &>/dev/null
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>0 */3 * * *</strong> /usr/bin/curl --silent '<strong><?php echo $cron; ?></strong>' &>/dev/null
					</div>

				</div>

				<div class="accordion-body">

					<div class="row" id="vhUpdateRow">

						<?php echo FlixCDNForm::group(
							'moduleUpdateType',
							'Спосіб запуску оновлення',
							FlixCDNForm::radio(
								'moduleUpdateTypeDefault',
								'settings[update][type]',
								'Стандартне оновлення',
								0,
								intval($flixcdn->config['update']['type'])
							) . '<div class="text-muted mb-2">Оновлення буде запускатися при відкритті сторінок сайту з інтервалом вказаним у налаштуванні "<b>Інтервал запуску оновлення</b>"</div>' . FlixCDNForm::radio(
								'moduleUpdateTypeCron',
								'settings[update][type]',
								'Планувальник завдань (<b>cron</b>)',
								1,
								intval($flixcdn->config['update']['type'])
							) . '<div class="text-muted">Оновлення буде запускатися за розкладом</div>',
							''
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleUpdateInterval',
							'Інтервал запуску оновлення',
							FlixCDNForm::select(
								'moduleUpdateInterval',
								'settings[update][interval]',
								array(
									'30m' => '30 хвилин',
									'1h' => '1 година',
									'2h' => '2 години',
									'3h' => '3 години',
								),
								$flixcdn->config['update']['interval'] ? $flixcdn->config['update']['interval'] : '3h'
							),
							'Інтервал запуску оновлення'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleUpdateCron',
							'',
							'<div class="alert alert-warning" style="margin-left:-15px;margin-right:-15px;border-radius:3px">
								Оновлення в планувальнику завдань (<b>cron</b>) ви налаштовуєте самі на своєму сервері/хостингу. Ви можете спробувати попросити допомоги в налаштуванні у підтримки сервера/хостингу.
							</div>
							<div class="alert alert-success mb-0" style="margin-left:-15px;margin-right:-15px;border-radius:3px">
								<h4 class="alert-heading mb-0" style="font-size:1rem">' . $cron . '</h4>
							</div>',
							''
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsNotUpdate',
							'Не оновлювати',
							FlixCDNForm::select(
								'moduleXfieldsNotUpdate',
								'settings[xfields][npt_update]',
								$not_update_xfields,
								$flixcdn->config['xfields']['npt_update']
							),
							'Дод. поле <b>Перемикач \'Так\' або \'Ні\'</b> для виключення новини з оновлення (якщо <b>Так</b>, новина не братиме участь в оновленні)'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Налаштування оновлення фільмів</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateMoviesOn',
							'Оновлення фільмів',
							FlixCDNForm::_switch(
								'moduleUpdateMoviesOn',
								'settings[update][movies][on]',
								$flixcdn->config['update']['movies']['on'] ? true : false
							),
							'Увімкнення та вимкнення оновлення фільмів'
						); ?>

					</div>
                    <div class="row">
                        <?php echo FlixCDNForm::group(
                                'publishImmediately',
                                'Публікувати пости без модерації',
                                FlixCDNForm::_switch(
                                        'publishImmediately',
                                        'settings[publish_immediately]',
                                        $flixcdn->config['publish_immediately'] ? true : false
                                ),
                                'При увімкненні нові пости будуть публікуватися одразу без модерації'
                        ); ?>
                    </div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateMovies',
							'Додаткові опції оновлення фільмів',
							FlixCDNForm::checkbox(
								'moduleUpdateMoviesUp',
								'settings[update][movies][up]',
								'Піднімати новину при виході кращої якості відео',
								$flixcdn->config['update']['movies']['up'] ? true : false
							) . FlixCDNForm::checkbox(
								'moduleUpdateMoviesAdd',
								'settings[update][movies][add]',
								'Додавати новину якщо фільм не знайдено на сайті<br>(потрапляє на модерацію)',
								$flixcdn->config['update']['movies']['add'] ? true : false
							),
							''
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Налаштування оновлення серіалів</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateSerialsOn',
							'Оновлення серіалів',
							FlixCDNForm::_switch(
								'moduleUpdateSerialsOn',
								'settings[update][serials][on]',
								$flixcdn->config['update']['serials']['on'] ? true : false
							),
							'Увімкнення та вимкнення оновлення серіалів'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<!-- <div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateSerialsPriority',
							'Пріоритет перекладів серіалів',
							'<div>
								<button id="serialsPriorityButton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#serialsPriorityModal">Налаштувати пріоритет перекладів</button>
							</div>',
							'Налаштування пріоритету перекладів серіалів'
						); ?>

					</div>

					<hr class="vh-separator"> -->
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateSerials',
							'Додаткові опції оновлення серіалів',
							FlixCDNForm::checkbox(
								'moduleUpdateSerialsUp',
								'settings[update][serials][up]',
								'Піднімати новину при виході нової серії серіалу',
								$flixcdn->config['update']['serials']['up'] ? true : false
							) . FlixCDNForm::checkbox(
								'moduleUpdateSerialsAdd',
								'settings[update][serials][add]',
								'Додавати новину якщо серіал не знайдено на сайті<br>(потрапляє на модерацію)',
								$flixcdn->config['update']['serials']['add'] ? true : false
							),
							''
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Блок оновлень серіалів</h4>

					<div class="alert alert-dismissible alert-primary" style="margin-left:-15px;margin-right:-15px;border-radius:3px;margin-top:5px">
					
						<div>
							<strong>{include file="flixcdn/widgets/updates.php"}</strong>
							&mdash; Вивід блоку оновлень серіалів у шаблоні
						</div>

						<hr class="vh-separator mt-2 mb-2">

						<div>
							Файл шаблону для редагування блоку оновлень серіалів знаходиться за цим шляхом &mdash; <strong>flixcdn/widgets/updates.tpl</strong>
						</div>

					</div>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSerialsUpdatesOn',
							'Блок оновлень серіалів',
							FlixCDNForm::_switch(
								'moduleSerialsUpdatesOn',
								'settings[serials][updates][on]',
								$flixcdn->config['serials']['updates']['on'] ? true : false
							),
							'Увімкнення та вимкнення виводу блоку оновлень серіалів'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSerialsUpdatesDays',
							'Кількість днів',
							FlixCDNForm::text(
								'moduleSerialsUpdatesDays',
								'settings[serials][updates][days]',
								$flixcdn->config['serials']['updates']['days'] ? $flixcdn->config['serials']['updates']['days'] : false,
								'7'
							),
							'Кількість днів за які виводити оновлення в блоці<br>(за замовчуванням останні <b>7</b> днів)'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSerialsUpdatesDayItems',
							'Макс. кількість записів',
							FlixCDNForm::text(
								'moduleSerialsUpdatesDayItems',
								'settings[serials][updates][items]',
								$flixcdn->config['serials']['updates']['items'] ? $flixcdn->config['serials']['updates']['items'] : false,
								''
							),
							'Максимальна кількість записів що виводиться в блоці за <b>1</b> день<br>(за замовчуванням не обмежено)'
						); ?>

					</div>

				</div>

			</div>

		</div>

	</div>

	<textarea name="settings[custom][qualities]" id="settingsCustomQualities" style="display: none"></textarea>
	<textarea name="settings[custom][translations]" id="settingsCustomTranslations" style="display: none"></textarea>
	<textarea name="settings[custom][genres]" id="settingsCustomGenres" style="display: none"></textarea>

	<textarea name="settings[update][serials][priority]" id="settingsUpdateSerialsPriority" style="display: none"></textarea>

</form>

<button type="button" class="btn btn-success mb-3" id="settingsSave">Зберегти</button>

<!-- Custom Quality Modal -->
<div class="modal fade" id="customQualityModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="customQualityModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customQualityModalLabel">Свої назви якостей</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body" style="padding-top:0">
				
				<div class="alert alert-warning">
					Після зміни назв якостей не забудьте закрити це вікно та зберегти налаштування.
				</div>

				<div id="customQualityList">
					<?php if ($flixcdn->config['custom']['qualities']) foreach ($flixcdn->config['custom']['qualities'] as $pattern => $replacement) { ?>
						<div class="form-inline custom-quality">
							<input type="text" class="form-control custom-quality-from" placeholder="Назва з бази" value="<?php echo flixcdn_encode($pattern); ?>">
							<input type="text" class="form-control custom-quality-to" placeholder="Своя назва" value="<?php echo flixcdn_encode($replacement); ?>">
							<button type="button" class="btn btn-danger custom-quality-delete" title="Видалити заміну"><i class="fas fa-trash"></i></button>
						</div>
					<?php } ?>
				</div>

				<button type="button" class="btn btn-success custom-quality-duplicate" title="Додати заміну">
					Додати заміну
				</button>

			</div>
		</div>
	</div>
</div>

<!-- Custom Translation Modal -->
<div class="modal fade" id="customTranslationModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="customTranslationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customTranslationModalLabel">Свої назви перекладів</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body" style="padding-top:0">
				
				<div class="alert alert-warning">
					Після зміни назв перекладів не забудьте закрити це вікно та зберегти налаштування.
				</div>

				<div id="customTranslationList">
					<?php if ($flixcdn->config['custom']['translations']) foreach ($flixcdn->config['custom']['translations'] as $pattern => $replacement) { ?>
						<div class="form-inline custom-translation">
							<input type="text" class="form-control custom-translation-from" placeholder="Назва з бази" value="<?php echo flixcdn_encode($pattern); ?>">
							<input type="text" class="form-control custom-translation-to" placeholder="Своя назва" value="<?php echo flixcdn_encode($replacement); ?>">
							<button type="button" class="btn btn-danger custom-translation-delete" title="Видалити заміну"><i class="fas fa-trash"></i></button>
						</div>
					<?php } ?>
				</div>

				<button type="button" class="btn btn-success custom-translation-duplicate" title="Додати заміну">
					Додати заміну
				</button>

			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customGenresModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="customGenresModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customGenresModalLabel">Відповідність жанрів</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body" style="padding-top:0">
				
				<div class="alert alert-warning">
					Після зміни відповідностей жанрів не забудьте закрити це вікно та зберегти налаштування.<br>
					<strong>Режим категорій:</strong> вкажіть ID існуючої категорії DLE<br>
					<strong>Режим дод. полів:</strong> вкажіть назву для заміни жанру
				</div>

				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-md-5"><strong>Жанр з API</strong></div>
						<div class="col-md-5"><strong>Категорія/Назва</strong></div>
						<div class="col-md-2"><strong>Дія</strong></div>
					</div>
				</div>
				
				<div id="customGenresList">
					<?php if ($flixcdn->config['custom']['genres']) foreach ($flixcdn->config['custom']['genres'] as $pattern => $replacement) { ?>
						<div class="row mb-2 custom-genre">
							<div class="col-md-5">
								<input type="text" class="form-control custom-genre-from" placeholder="Жанр з API" value="<?php echo flixcdn_encode($pattern); ?>">
							</div>
							<div class="col-md-5">
								<input type="text" class="form-control custom-genre-to" placeholder="ID категорії або назва" value="<?php echo flixcdn_encode($replacement); ?>">
							</div>
							<div class="col-md-2">
								<button type="button" class="btn btn-danger custom-genre-delete w-100" title="Видалити відповідність"><i class="fas fa-trash"></i></button>
							</div>
						</div>
					<?php } ?>
				</div>

				<button type="button" class="btn btn-success custom-genre-duplicate" title="Додати відповідність">
					Додати відповідність
				</button>

			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var genresData = <?php echo json_encode($genres); ?>;
var categoriesData = <?php echo json_encode($categories); ?>;
var currentStorageMode = '<?php echo $flixcdn->config['genres_storage'] ?? 'xfields'; ?>';
</script>

<?php if (false) { ?>

<!-- Serials Priority Modal -->
<div class="modal fade" id="serialsPriorityModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="serialsPriorityModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="serialsPriorityModalLabel">Пріоритет перекладів</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="alert alert-primary">
					Після зміни пріоритету перекладів не забудьте закрити це вікно та зберегти налаштування.
				</div>

				<div class="vh-priority">
					
					<div id="serialsPriorityContainer" class="sort-container">
						<?php
							if ($flixcdn->config['update']['serials']['priority']) foreach ($flixcdn->config['update']['serials']['priority'] as $key) {
								if (!$flixcdn->config['translations'][$key])
									continue;
								else
									$translation = $flixcdn->config['translations'][$key];
						?>
							<div id="serialsTranslation<?=$key?>" class="sortable sortable-selected btn btn-outline-info" data-id="<?=$key?>"><?=$translation?><a href="javascript:void(0)" data-id="<?=$key?>" title="Видалити переклад"><i class="fas fa-times"></i></a></div>
						<?php } ?>
					</div>

					<div id="serialsNoPriorityContainer" class="sort-container">
						<?php
							if ($flixcdn->config['translations']) foreach ($flixcdn->config['translations'] as $key => $translation) {
								if (in_array($key, $flixcdn->config['update']['serials']['priority']))
									continue;
							?>
							<div id="serialsTranslation<?=$key?>" class="sortable btn btn-outline-secondary" data-id="<?=$key?>"><?=$translation?><a href="javascript:void(0)" data-id="<?=$key?>" title="Додати переклад"><i class="fas fa-plus"></i></a></div>
						<?php } ?>
					</div>
					
				</div>

			</div>
		</div>
	</div>
</div>

<?php } ?>

<?php

include dirname(__FILE__) . '/footer.php';