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

$pageTitle = 'FlixCDN - Настройки модуля';

include dirname(__FILE__) . '/header.php';

?>

<form id="settingsForm" action="<?php echo flixcdn_action('settings'); ?>" method="post">

	<div class="accordion mb-2" id="accordionSettings">

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingOther">
	      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOther" aria-expanded="true" aria-controls="collapseOther">
	        Общие настройки
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
							'Включение и выключение работы модуля'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingApi">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApi" aria-expanded="false" aria-controls="collapseApi">
	        Настройки доступа к API
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
							'Ваш персональный API Ключ'
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
							'Домен для доступа к API (не обязательно)'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingPlayer">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlayer" aria-expanded="false" aria-controls="collapsePlayer">
	        Настройки вывода плеера
	      </button>
	    </h2>

			<div id="collapsePlayer" class="accordion-collapse collapse" aria-labelledby="headingPlayer" data-bs-parent="#accordionSettings" style="">
      	<!-- <div class="alert alert-dismissible alert-primary mb-0" style="margin:5px;border-radius:3px"> -->
      	<div class="alert alert-dismissible alert-primary" style="margin:5px;border-radius:3px">
				
					<div>
						<strong>[flixcdn-found]{flixcdn-player}[/flixcdn-found]</strong>
						&mdash; Вывод плеера в шаблоне полной новости (<strong>fullstory.tpl</strong>)
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[flixcdn-notfound] ... [/flixcdn-notfound]</strong>
						&mdash; Код в этих тегах будет выведен если ссылка на источник плеера не заполнена (<strong>fullstory.tpl</strong>)
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
	        Настройки доп. полей
	      </button>
	    </h2>

			<div id="collapseXfields" class="accordion-collapse collapse" aria-labelledby="headingXfields" data-bs-parent="#accordionSettings" style="">
      	<div class="accordion-body">
				
					<h4 class="card-header sub-card-header mb-3">Обязательные поля для работы модуля</h4>

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
							'Доп. поле для поиска по Kinopoisk ID'
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
							'Доп. поле для поиска по Imdb ID'
						); ?>

					</div>

					<hr class="vh-separator">

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteIframe',
							'Источник видео',
							FlixCDNForm::select(
								'moduleXfieldsWriteIframe',
								'settings[xfields][write][source]',
								$xfields,
								$flixcdn->config['xfields']['write']['source']
							),
							'Доп. поле для заполнения источника видео (ссылка для вывода плеера)'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteQuality',
							'Качество видео',
							FlixCDNForm::select(
								'moduleXfieldsWriteQuality',
								'settings[xfields][write][quality]',
								$xfields,
								$flixcdn->config['xfields']['write']['quality']
							),
							'Доп. поле для заполнения качества видео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTranslation',
							'Перевод',
							FlixCDNForm::select(
								'moduleXfieldsWriteTranslation',
								'settings[xfields][write][translation]',
								$xfields,
								$flixcdn->config['xfields']['write']['translation']
							),
							'Доп. поле для заполнения перевода видео'
						); ?>

					</div>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTranslations',
							'Список переводов сериала',
							FlixCDNForm::select(
								'moduleXfieldsWriteTranslations',
								'settings[xfields][write][translations]',
								$xfields,
								$flixcdn->config['xfields']['write']['translations']
							),
							'Доп. поле для заполнения списка всех переводов сериала'
						); ?>

					</div>

					<hr class="vh-separator">

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteSeason',
							'Номер последнего сезона',
							FlixCDNForm::select(
								'moduleXfieldsWriteSeason',
								'settings[xfields][write][season]',
								$xfields,
								$flixcdn->config['xfields']['write']['season']
							),
							'Доп. поле для заполнения номера последнего сезона сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteEpisode',
							'Номер последней серии',
							FlixCDNForm::select(
								'moduleXfieldsWriteEpisode',
								'settings[xfields][write][episode]',
								$xfields,
								$flixcdn->config['xfields']['write']['episode']
							),
							'Доп. поле для заполнения номера последней серии сериала'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Альтернативный вывод данных</h4>
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomQualitySet',
							'Список своих названий для качества видео',
							'<div>
								<button id="customQualityButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customQualityModal">Настроить свои названия качеств</button>
							</div>',
							'Настройки своих названий для качества видео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomQuality',
							'Качество видео (с заменой)',
							FlixCDNForm::select(
								'moduleXfieldsWriteCustomQuality',
								'settings[xfields][write][custom_quality]',
								$xfields,
								$flixcdn->config['xfields']['write']['custom_quality']
							),
							'Доп. поле для заполнения качества видео с заменой названий'
						); ?>

					</div>

					<hr class="vh-separator">

					<h4 class="card-header sub-card-header mb-3">Настройки жанров</h4>
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'genresStorageMode',
							'Способ хранения жанров',
							FlixCDNForm::radio(
								'genresStorageModeXfields',
								'settings[genres_storage]',
								'В доп. поля (как текст)',
								'xfields',
								$flixcdn->config['genres_storage'] ?? 'xfields'
							) . '<div class="text-muted mb-2">Жанры будут сохраняться как текст в указанное дополнительное поле</div>' . FlixCDNForm::radio(
								'genresStorageModeCategories',
								'settings[genres_storage]',
								'В категории DLE',
								'categories',
								$flixcdn->config['genres_storage'] ?? 'xfields'
							) . '<div class="text-muted mb-2">Жанры будут привязываться к существующим категориям DLE</div>',
							'Выберите способ хранения жанров в системе'
						); ?>

						<div class="col-12" id="genresXfieldSelection" style="display: <?php echo ($flixcdn->config['genres_storage'] ?? 'xfields') === 'xfields' ? 'block' : 'none'; ?>;">
							<?php echo FlixCDNForm::group(
								'moduleXfieldsWriteGenresCustom',
								'Доп. поле для жанров',
								FlixCDNForm::select(
									'moduleXfieldsWriteGenresCustom',
									'settings[xfields][write][genres_custom]',
									$xfields,
									$flixcdn->config['xfields']['write']['genres_custom'] ?? ''
								),
								'Доп. поле для сохранения жанров (при выборе режима "В доп. поля")'
							); ?>
						</div>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomGenresSet',
							'Маппинг жанров',
							'<div>
								<button id="customGenresButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customGenresModal">Настроить соответствие жанров</button>
							</div>',
							'Настройки соответствия жанров из API к категориям или названиям'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomTranslationSet',
							'Список своих названий для переводов',
							'<div>
								<button id="customTranslationButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customTranslationModal">Настроить свои названия переводов</button>
							</div>',
							'Настройки своих названий для переводов видео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomTranslation',
							'Перевод (с заменой)',
							FlixCDNForm::select(
								'moduleXfieldsWriteCustomTranslation',
								'settings[xfields][write][custom_translation]',
								$xfields,
								$flixcdn->config['xfields']['write']['custom_translation']
							),
							'Доп. поле для заполнения перевода видео с заменой названий'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCustomTranslations',
							'Список переводов сериала (с заменой)',
							FlixCDNForm::select(
								'moduleXfieldsWriteCustomTranslations',
								'settings[xfields][write][custom_translations]',
								$xfields,
								$flixcdn->config['xfields']['write']['custom_translations']
							),
							'Доп. поле для заполнения списка всех переводов сериала с заменой названий'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatSeasonType',
							'Тип форматирования сезона',
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
							'Тип форматирования сезона сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatSeason',
							'Форматированный сезон',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatSeason',
								'settings[xfields][write][format_season]',
								$xfields,
								$flixcdn->config['xfields']['write']['format_season']
							),
							'Доп. поле для заполнения форматированного сезона сериала'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatEpisodeType',
							'Тип форматирования серии',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatEpisodeType',
								'settings[xfields][write][format_episode_type]',
								array(
									0 => '',
									1 => '1 серия, 2 серия, 3 серия',
									2 => '1 серия, 1-2 серия, 1-3 серия, 1-4 серия',
									3 => '1 серия, 1,2 серия, 1,2,3 серия, 1,2,3,4 серия',
									4 => '1 серия, 1,2 серия, 1,2,3 серия, 2,3,4 серия',
									5 => '1,2 серия, 1,2,3 серия, 1,2,3 серия, 1,2,3,4,5 серия, 1-5,6,7 серия'
								),
								$flixcdn->config['xfields']['write']['format_episode_type']
							),
							'Тип форматирования серии сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteFormatEpisode',
							'Форматированная серия',
							FlixCDNForm::select(
								'moduleXfieldsWriteFormatEpisode',
								'settings[xfields][write][format_episode]',
								$xfields,
								$flixcdn->config['xfields']['write']['format_episode']
							),
							'Доп. поле для заполнения форматированной серии сериала'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Дополнительные поля для вывода данных</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTitleRu',
							'Название на Русском',
							FlixCDNForm::select(
								'moduleXfieldsWriteTitleRu',
								'settings[xfields][write][title_rus]',
								$xfields,
								$flixcdn->config['xfields']['write']['title_rus']
							),
							'Доп. поле для заполнения названия фильма или сериала на Русском языке'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteTitleOrig',
							'Оригинальное название',
							FlixCDNForm::select(
								'moduleXfieldsWriteTitleOrig',
								'settings[xfields][write][title_orig]',
								$xfields,
								$flixcdn->config['xfields']['write']['title_orig']
							),
							'Доп. поле для заполнения Оригинального названия фильма или сериала'
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
							'Доп. поле для заполнения слогана фильма или сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteDescription',
							'Описание',
							FlixCDNForm::select(
								'moduleXfieldsWriteDescription',
								'settings[xfields][write][description]',
								$xfields,
								$flixcdn->config['xfields']['write']['description']
							),
							'Доп. поле для заполнения Описания фильма или сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteYear',
							'Год выпуска',
							FlixCDNForm::select(
								'moduleXfieldsWriteYear',
								'settings[xfields][write][year]',
								$xfields,
								$flixcdn->config['xfields']['write']['year']
							),
							'Доп. поле для заполнения Года выпуска фильма или сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteDuration',
							'Продолжительность видео',
							FlixCDNForm::select(
								'moduleXfieldsWriteDuration',
								'settings[xfields][write][duration]',
								$xfields,
								$flixcdn->config['xfields']['write']['duration']
							),
							'Доп. поле для заполнения Продолжительности видео'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteGenres',
							'Жанры',
							FlixCDNForm::select(
								'moduleXfieldsWriteGenres',
								'settings[xfields][write][genres]',
								$xfields,
								$flixcdn->config['xfields']['write']['genres']
							),
							'Доп. поле для заполнения списка Жанров фильма или сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteCountries',
							'Страны',
							FlixCDNForm::select(
								'moduleXfieldsWriteCountries',
								'settings[xfields][write][countries]',
								$xfields,
								$flixcdn->config['xfields']['write']['countries']
							),
							'Доп. поле для заполнения списка Стран фильма или сериала'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleXfieldsWriteAge',
							'Возрастное ограничение',
							FlixCDNForm::select(
								'moduleXfieldsWriteAge',
								'settings[xfields][write][age]',
								$xfields,
								$flixcdn->config['xfields']['write']['age']
							),
							'Доп. поле для заполнения Возрастного ограничения фильма или сериала'
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
							'Доп. поле для заполнения ссылки на постер фильма или сериала'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingSeo">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeo" aria-expanded="false" aria-controls="collapseSeo">
	        Шаблоны СЕО данных
	      </button>
	    </h2>

			<div id="collapseSeo" class="accordion-collapse collapse" aria-labelledby="headingSeo" data-bs-parent="#accordionSettings" style="">
      	
				<div class="alert alert-dismissible alert-primary mb-0" style="margin:5px;border-radius:3px">
					
					<div>
						<strong>[movie] ... [/movie]</strong>
						&mdash; Текст заключённый в эти теги будет использоваться только для фильмов
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[serial] ... [/serial]</strong>
						&mdash; Текст заключённый в эти теги будет использоваться только для сериалов
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[year]{year}[/year]</strong>
						&mdash; Год выпуска фильма или сериала
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[title_rus]{title_rus}[/title_rus]</strong>
						&mdash; Название фильма или сериала на Русском языке
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[title_orig]{title_orig}[/title_orig]</strong>
						&mdash; Оригинальное название фильма или сериала
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[quality]{quality}[/quality]</strong>
						&mdash; Качество видео фильма или сериала
					</div>

					<hr class="vh-separator mt-2 mb-2">
					
					<div>
						<strong>[translation]{translation}[/translation]</strong>
						&mdash; Перевод фильма или сериала
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[season]{season}[/season]</strong>
						&mdash; Номер последнего сезона сериала
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[episode]{episode}[/episode]</strong>
						&mdash; Номер последней вышедшей серии сериала
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[custom_quality]{custom_quality}[/custom_quality]</strong>
						&mdash; Качество видео фильма или сериала<br>(с заменой на свои названия)
					</div>

					<hr class="vh-separator mt-2 mb-2">
					
					<div>
						<strong>[custom_translation]{custom_translation}[/custom_translation]</strong>
						&mdash; Перевод фильма или сериала<br>(с заменой на свои названия)
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[format_season]{format_season}[/format_season]</strong>
						&mdash; Форматированный вывод сезона сериала<br>(тип форматирования указывается в разделе "Настройки полей для заполнения")
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[format_episode]{format_episode}[/format_episode]</strong>
						&mdash; Форматированный вывод серии сериала<br>(тип форматирования указывается в разделе "Настройки полей для заполнения")
					</div>

				</div>

				<div class="accordion-body">

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSeoOn',
							'Заполнение СЕО данных',
							FlixCDNForm::_switch(
								'moduleSeoOn',
								'settings[seo][on]',
								$flixcdn->config['seo']['on'] ? true : false
							),
							'Включение и выключение заполнения СЕО данных'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSeoUrl',
							'ЧПУ новости',
							FlixCDNForm::text(
								'moduleSeoUrl',
								'settings[seo][url]',
								$flixcdn->config['seo']['url'] ? $flixcdn->config['seo']['url'] : false,
								''
							),
							'Шаблон заполнения ЧПУ новости (переводится в транслит)'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSeoTitle',
							'Заголовок новости',
							FlixCDNForm::text(
								'moduleSeoTitle',
								'settings[seo][title]',
								$flixcdn->config['seo']['title'] ? $flixcdn->config['seo']['title'] : false,
								''
							),
							'Шаблон заполнения заголовока новости'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSeoMetaTitle',
							'Мета-заголовок новости',
							FlixCDNForm::text(
								'moduleSeoMetaTitle',
								'settings[seo][meta][title]',
								$flixcdn->config['seo']['meta']['title'] ? $flixcdn->config['seo']['meta']['title'] : false,
								''
							),
							'Шаблон заполнения мета-заголовока новости'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSeoMetaDescription',
							'Мета-описание новости',
							FlixCDNForm::text(
								'moduleSeoMetaDescription',
								'settings[seo][meta][description]',
								$flixcdn->config['seo']['meta']['description'] ? $flixcdn->config['seo']['meta']['description'] : false,
								''
							),
							'Шаблон заполнения мета-описания новости'
						); ?>

					</div>

				</div>
			</div>

		</div>

		<div class="accordion-item">

			<h2 class="accordion-header" id="headingUpdate">
	      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUpdate" aria-expanded="false" aria-controls="collapseUpdate">
	        Настройки обновления
	      </button>
	    </h2>

			<div id="collapseUpdate" class="accordion-collapse collapse" aria-labelledby="headingUpdate" data-bs-parent="#accordionSettings" style="">

				<div class="alert alert-dismissible alert-primary mb-0 cron-doc" style="margin:5px;border-radius:3px">
				
					<h4>Пример настройки <strong>crontab</strong> на сервере</h4>

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
							'Способ запуска обновления',
							FlixCDNForm::radio(
								'moduleUpdateTypeDefault',
								'settings[update][type]',
								'Стандартное обновление',
								0,
								intval($flixcdn->config['update']['type'])
							) . '<div class="text-muted mb-2">Обновление будет запускаться при открытии страниц сайта с интервалом указанным в настройке "<b>Интервал запуска обновления</b>"</div>' . FlixCDNForm::radio(
								'moduleUpdateTypeCron',
								'settings[update][type]',
								'Планировщик задач (<b>cron</b>)',
								1,
								intval($flixcdn->config['update']['type'])
							) . '<div class="text-muted">Обновление будет запускаться по расписанию</div>',
							''
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleUpdateInterval',
							'Интервал запуска обновления',
							FlixCDNForm::select(
								'moduleUpdateInterval',
								'settings[update][interval]',
								array(
									'30m' => '30 минут',
									'1h' => '1 час',
									'2h' => '2 часа',
									'3h' => '3 часа',
								),
								$flixcdn->config['update']['interval'] ? $flixcdn->config['update']['interval'] : '3h'
							),
							'Интервал запуска обновления'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleUpdateCron',
							'',
							'<div class="alert alert-warning" style="margin-left:-15px;margin-right:-15px;border-radius:3px">
								Обновление в прланировщике задач (<b>cron</b>) вы настраиваете сами у себя на сервере/хостинге. Вы можете попробовать попросить помощи в настройке у поддержки сервера/хостинга.
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
							'Не обновлять',
							FlixCDNForm::select(
								'moduleXfieldsNotUpdate',
								'settings[xfields][npt_update]',
								$not_update_xfields,
								$flixcdn->config['xfields']['npt_update']
							),
							'Доп. поле <b>Переключатель \'Да\' или \'Нет\'</b> для исключения новости из обновления (если <b>Да</b>, новость участвовать в обновлении не будет)'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Настройки обновления фильмов</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateMoviesOn',
							'Обновление фильмов',
							FlixCDNForm::_switch(
								'moduleUpdateMoviesOn',
								'settings[update][movies][on]',
								$flixcdn->config['update']['movies']['on'] ? true : false
							),
							'Включение и выключение обновления фильмов'
						); ?>

					</div>
                    <div class="row">
                        <?php echo FlixCDNForm::group(
                                'publishImmediately',
                                'Публиковать посты без модерации',
                                FlixCDNForm::_switch(
                                        'publishImmediately',
                                        'settings[publish_immediately]',
                                        $flixcdn->config['publish_immediately'] ? true : false
                                ),
                                'При включении новые посты будут публиковаться сразу без модерации'
                        ); ?>
                    </div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateMovies',
							'Дополнительные опции обновления фильмов',
							FlixCDNForm::checkbox(
								'moduleUpdateMoviesUp',
								'settings[update][movies][up]',
								'Поднимать новость при выходе лучшего качества видео',
								$flixcdn->config['update']['movies']['up'] ? true : false
							) . FlixCDNForm::checkbox(
								'moduleUpdateMoviesAdd',
								'settings[update][movies][add]',
								'Добавлять новость если фильм не найден на сайте<br>(попадает на модерацию)',
								$flixcdn->config['update']['movies']['add'] ? true : false
							),
							''
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Настройки обновления сериалов</h4>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateSerialsOn',
							'Обновление сериалов',
							FlixCDNForm::_switch(
								'moduleUpdateSerialsOn',
								'settings[update][serials][on]',
								$flixcdn->config['update']['serials']['on'] ? true : false
							),
							'Включение и выключение обновления сериалов'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<!-- <div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateSerialsPriority',
							'Приоритет переводов сериалов',
							'<div>
								<button id="serialsPriorityButton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#serialsPriorityModal">Настроить приоритет переводов</button>
							</div>',
							'Настройки приориета переводов сериалов'
						); ?>

					</div>

					<hr class="vh-separator"> -->
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleUpdateSerials',
							'Дополнительные опции обновления сериалов',
							FlixCDNForm::checkbox(
								'moduleUpdateSerialsUp',
								'settings[update][serials][up]',
								'Поднимать новость при выходе новой серии сериала',
								$flixcdn->config['update']['serials']['up'] ? true : false
							) . FlixCDNForm::checkbox(
								'moduleUpdateSerialsAdd',
								'settings[update][serials][add]',
								'Добавлять новость если сериал не найден на сайте<br>(попадает на модерацию)',
								$flixcdn->config['update']['serials']['add'] ? true : false
							),
							''
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Блок обновлений сериалов</h4>

					<div class="alert alert-dismissible alert-primary" style="margin-left:-15px;margin-right:-15px;border-radius:3px;margin-top:5px">
					
						<div>
							<strong>{include file="flixcdn/widgets/updates.php"}</strong>
							&mdash; Вывод блока обвновлений сериалов в шаблоне
						</div>

						<hr class="vh-separator mt-2 mb-2">

						<div>
							Файл шаблона для редактирования блока обновлений сериалов находится по этому пути &mdash; <strong>flixcdn/widgets/updates.tpl</strong>
						</div>

					</div>

					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSerialsUpdatesOn',
							'Блок обновлений сериалов',
							FlixCDNForm::_switch(
								'moduleSerialsUpdatesOn',
								'settings[serials][updates][on]',
								$flixcdn->config['serials']['updates']['on'] ? true : false
							),
							'Включение и выключение вывода блока обновлений сериалов'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo FlixCDNForm::group(
							'moduleSerialsUpdatesDays',
							'Кол-во дней',
							FlixCDNForm::text(
								'moduleSerialsUpdatesDays',
								'settings[serials][updates][days]',
								$flixcdn->config['serials']['updates']['days'] ? $flixcdn->config['serials']['updates']['days'] : false,
								'7'
							),
							'Кол-во дней за которое выводить обновления в блоке<br>(по умолчанию последние <b>7</b> дней)'
						); ?>

						<?php echo FlixCDNForm::group(
							'moduleSerialsUpdatesDayItems',
							'Макс. кол-во записей',
							FlixCDNForm::text(
								'moduleSerialsUpdatesDayItems',
								'settings[serials][updates][items]',
								$flixcdn->config['serials']['updates']['items'] ? $flixcdn->config['serials']['updates']['items'] : false,
								''
							),
							'Максимальное кол-во записей выводимое в блоке за <b>1</b> день<br>(по умолчанию не ограничено)'
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

<button type="button" class="btn btn-success mb-3" id="settingsSave">Сохранить</button>

<!-- Custom Quality Modal -->
<div class="modal fade" id="customQualityModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="customQualityModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customQualityModalLabel">Свои названия качеств</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body" style="padding-top:0">
				
				<div class="alert alert-warning">
					После изменения названий качеств не забудьте закрыть это окно и сохранить настройки.
				</div>

				<div id="customQualityList">
					<?php if ($flixcdn->config['custom']['qualities']) foreach ($flixcdn->config['custom']['qualities'] as $pattern => $replacement) { ?>
						<div class="form-inline custom-quality">
							<input type="text" class="form-control custom-quality-from" placeholder="Название из базы" value="<?php echo flixcdn_encode($pattern); ?>">
							<input type="text" class="form-control custom-quality-to" placeholder="Своё название" value="<?php echo flixcdn_encode($replacement); ?>">
							<button type="button" class="btn btn-danger custom-quality-delete" title="Удалить замену"><i class="fas fa-trash"></i></button>
						</div>
					<?php } ?>
				</div>

				<button type="button" class="btn btn-success custom-quality-duplicate" title="Добавить замену">
					Добавить замену
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
				<h5 class="modal-title" id="customTranslationModalLabel">Свои названия переводов</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body" style="padding-top:0">
				
				<div class="alert alert-warning">
					После изменения названий переводов не забудьте закрыть это окно и сохранить настройки.
				</div>

				<div id="customTranslationList">
					<?php if ($flixcdn->config['custom']['translations']) foreach ($flixcdn->config['custom']['translations'] as $pattern => $replacement) { ?>
						<div class="form-inline custom-translation">
							<input type="text" class="form-control custom-translation-from" placeholder="Название из базы" value="<?php echo flixcdn_encode($pattern); ?>">
							<input type="text" class="form-control custom-translation-to" placeholder="Своё название" value="<?php echo flixcdn_encode($replacement); ?>">
							<button type="button" class="btn btn-danger custom-translation-delete" title="Удалить замену"><i class="fas fa-trash"></i></button>
						</div>
					<?php } ?>
				</div>

				<button type="button" class="btn btn-success custom-translation-duplicate" title="Добавить замену">
					Добавить замену
				</button>

			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customGenresModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="customGenresModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customGenresModalLabel">Соответствие жанров</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body" style="padding-top:0">
				
				<div class="alert alert-warning">
					После изменения соответствий жанров не забудьте закрыть это окно и сохранить настройки.<br>
					<strong>Режим категорий:</strong> укажите ID существующей категории DLE<br>
					<strong>Режим доп. полей:</strong> укажите название для замены жанра
				</div>

				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-md-5"><strong>Жанр из API</strong></div>
						<div class="col-md-5"><strong>Категория/Название</strong></div>
						<div class="col-md-2"><strong>Действие</strong></div>
					</div>
				</div>
				
				<div id="customGenresList">
					<?php if ($flixcdn->config['custom']['genres']) foreach ($flixcdn->config['custom']['genres'] as $pattern => $replacement) { ?>
						<div class="row mb-2 custom-genre">
							<div class="col-md-5">
								<input type="text" class="form-control custom-genre-from" placeholder="Жанр из API" value="<?php echo flixcdn_encode($pattern); ?>">
							</div>
							<div class="col-md-5">
								<input type="text" class="form-control custom-genre-to" placeholder="ID категории или название" value="<?php echo flixcdn_encode($replacement); ?>">
							</div>
							<div class="col-md-2">
								<button type="button" class="btn btn-danger custom-genre-delete w-100" title="Удалить соответствие"><i class="fas fa-trash"></i></button>
							</div>
						</div>
					<?php } ?>
				</div>

				<button type="button" class="btn btn-success custom-genre-duplicate" title="Добавить соответствие">
					Добавить соответствие
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
				<h5 class="modal-title" id="serialsPriorityModalLabel">Приоритет переводов</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="alert alert-primary">
					После изменений приоритета переводов не забудьте закрыть это окно и сохранить настройки.
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
							<div id="serialsTranslation<?=$key?>" class="sortable sortable-selected btn btn-outline-info" data-id="<?=$key?>"><?=$translation?><a href="javascript:void(0)" data-id="<?=$key?>" title="Удалить перевод"><i class="fas fa-times"></i></a></div>
						<?php } ?>
					</div>

					<div id="serialsNoPriorityContainer" class="sort-container">
						<?php
							if ($flixcdn->config['translations']) foreach ($flixcdn->config['translations'] as $key => $translation) {
								if (in_array($key, $flixcdn->config['update']['serials']['priority']))
									continue;
							?>
							<div id="serialsTranslation<?=$key?>" class="sortable btn btn-outline-secondary" data-id="<?=$key?>"><?=$translation?><a href="javascript:void(0)" data-id="<?=$key?>" title="Добавить перевод"><i class="fas fa-plus"></i></a></div>
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