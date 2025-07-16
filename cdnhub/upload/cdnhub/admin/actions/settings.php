<?php

// Save

if (isset($_POST['settings'])) {

	// Cronkey

	if ($cdnhub->config['cronkey'])
		$cronkey = $cdnhub->config['cronkey'];

	// Domain

	if ($cdnhub->config['domain'])
		$domain = $cdnhub->config['domain'];

	if ($cdnhub->config['domain_update'])
		$domain_update = $cdnhub->config['domain_update'];

	$cdnhub->config = $_POST['settings'];

	// Custom Qualities

	if ($cdnhub->config['custom']['qualities']) {

		$custom_qualities = array();

		$data = explode("\r\n", $cdnhub->config['custom']['qualities']);

		if ($data) foreach ($data as $string) {
			list($pattern, $replacement) = explode('|', $string);

			if ($pattern)
				$custom_qualities[$pattern] = $replacement;
		}

		$cdnhub->config['custom']['qualities'] = $custom_qualities;

	} else
		$cdnhub->config['custom']['qualities'] = array();

	// Custom Translations

	if ($cdnhub->config['custom']['translations']) {

		$custom_translations = array();

		$data = explode("\r\n", $cdnhub->config['custom']['translations']);

		if ($data) foreach ($data as $string) {
			list($pattern, $replacement) = explode('|', $string);

			if ($pattern)
				$custom_translations[$pattern] = $replacement;
		}

		$cdnhub->config['custom']['translations'] = $custom_translations;

	} else
		$cdnhub->config['custom']['translations'] = array();

	// Translations

	$cdnhubApi = new CDNHubApi($cdnhub->config['api']);

	$translations = $cdnhubApi->getTranslations();

	// Save

	if ($translations)
		$cdnhub->config['translations'] = $translations;

	if ($cronkey)
		$cdnhub->config['cronkey'] = $cronkey;

	if ($domain)
		$cdnhub->config['domain'] = $domain;

	if ($domain_update)
		$cdnhub->config['domain_update'] = $domain_update;

	if ($cdnhub->config['update']['serials']['priority'])
		$cdnhub->config['update']['serials']['priority'] = explode(',', $cdnhub->config['update']['serials']['priority']);

	$fh = fopen(CDNHUB_DIR . '/config.php', 'w');
	fwrite($fh, '<?php' . "\r\n\r\nreturn " . var_export($cdnhub->config, true) . ';');
	fclose($fh);

	echo json_encode(array('status' => 'success'));
	exit;

}

// Translations

/*if ($cdnhub->config['api']['token']) {

	$cdnhubApi = new CDNHubApi($cdnhub->config['api']);

	$data = $cdnhubApi->getTranslations();

	if ($data && !$data['code']) {
		$translations = array();

		foreach ($data as $translation)
			$translations[intval($translation['id'])] = $translation['name'];

		if ($translations) {
			$cdnhub->config['translations'] = $translations;

			$fh = fopen(CDNHUB_DIR . '/config.php', 'w');
			fwrite($fh, '<?php' . "\r\n\r\nreturn " . var_export($cdnhub->config, true) . ';');
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

if (!$cdnhub->config['cronkey']) {

	$cronkey = md5($config['http_home_url'] . time());

	$cdnhub->config['cronkey'] = $cronkey;

	$fh = fopen(CDNHUB_DIR . '/config.php', 'w');
	fwrite($fh, '<?php' . "\r\n\r\nreturn " . var_export($cdnhub->config, true) . ';');
	fclose($fh);

}

$cron = "{$config['http_home_url']}cdnhub.php?key={$cdnhub->config['cronkey']}";

// Qualities

$cdnhubUpdate = new CDNHubUpdate($cdnhub->config);

$qualities = array();

foreach ($cdnhubUpdate->quality as $quality)
	$qualities[] = $quality;

// Qualities

$translations = array();

if ($cdnhub->config['translations']) foreach ($cdnhub->config['translations'] as $translation)
	$translations[] = $translation;

// Settings

$pageTitle = 'CDNHub - Настройки модуля';

include dirname(__FILE__) . '/header.php';

?>

<form id="settingsForm" action="<?php echo cdnhub_action('settings'); ?>" method="post">

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
								
						<?php echo CDNHubForm::group(
							'moduleOn',
							'Модуль',
							CDNHubForm::_switch(
								'moduleOn',
								'settings[on]',
								$cdnhub->config['on'] ? true : false
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

						<?php echo CDNHubForm::group(
							'moduleApiToken',
							'API Ключ',
							CDNHubForm::text(
								'moduleApiToken',
								'settings[api][token]',
								$cdnhub->config['api']['token'] ? $cdnhub->config['api']['token'] : false,
								'API Ключ'
							),
							'Ваш персональный API Ключ'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleApiDomain',
							'API Домен',
							CDNHubForm::text(
								'moduleApiDomain',
								'settings[api][domain]',
								$cdnhub->config['api']['domain'] ? $cdnhub->config['api']['domain'] : false,
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
						<strong>[cdnhub-found]{cdnhub-player}[/cdnhub-found]</strong>
						&mdash; Вывод плеера в шаблоне полной новости (<strong>fullstory.tpl</strong>)
					</div>

					<hr class="vh-separator mt-2 mb-2">

					<div>
						<strong>[cdnhub-notfound] ... [/cdnhub-notfound]</strong>
						&mdash; Код в этих тегах будет выведен если ссылка на источник плеера не заполнена (<strong>fullstory.tpl</strong>)
					</div>

				</div>

      	<div class="accordion-body" style="display:none">

					<div class="row">

						<?php /*echo CDNHubForm::group(
							'modulePlayerD',
							'Основной домен сайта',
							CDNHubForm::text(
								'modulePlayerD',
								'settings[d]',
								$cdnhub->config['d'] ? $cdnhub->config['d'] : false,
								'example.com'
							),
							'Основной домен/зеркало вашего сайта<br>(обязательно указывать для корректного вывода статистики в личном кабинете веб-мастера)'
						);*/ ?>

						<?php /*echo CDNHubForm::group(
							'modulePlyerScript',
							'JS Скрипт',
							CDNHubForm::text(
								'modulePlyerScript',
								'settings[player][script]',
								$cdnhub->config['player']['script'] ? $cdnhub->config['player']['script'] : false,
								'https://example.com/script.js'
							),
							'Скрипт для замены не рабочего домена плеера'
						);*/ ?>

						<?php /*echo CDNHubForm::group(
							'modulePlayerParams',
							'Глобальные параметры плеера',
							CDNHubForm::text(
								'modulePlayerParams',
								'settings[player][params]',
								$cdnhub->config['player']['params'] ? $cdnhub->config['player']['params'] : false,
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

						<?php echo CDNHubForm::group(
							'moduleXfieldsSearchKinopoisk',
							'Kinopoisk ID',
							CDNHubForm::select(
								'moduleXfieldsSearchKinopoisk',
								'settings[xfields][search][kinopoisk_id]',
								$xfields,
								$cdnhub->config['xfields']['search']['kinopoisk_id']
							),
							'Доп. поле для поиска по Kinopoisk ID'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsSearchImdb',
							'Imdb ID',
							CDNHubForm::select(
								'moduleXfieldsSearchImdb',
								'settings[xfields][search][imdb_id]',
								$xfields,
								$cdnhub->config['xfields']['search']['imdb_id']
							),
							'Доп. поле для поиска по Imdb ID'
						); ?>

					</div>

					<hr class="vh-separator">

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteIframe',
							'Источник видео',
							CDNHubForm::select(
								'moduleXfieldsWriteIframe',
								'settings[xfields][write][source]',
								$xfields,
								$cdnhub->config['xfields']['write']['source']
							),
							'Доп. поле для заполнения источника видео (ссылка для вывода плеера)'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteQuality',
							'Качество видео',
							CDNHubForm::select(
								'moduleXfieldsWriteQuality',
								'settings[xfields][write][quality]',
								$xfields,
								$cdnhub->config['xfields']['write']['quality']
							),
							'Доп. поле для заполнения качества видео'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteTranslation',
							'Перевод',
							CDNHubForm::select(
								'moduleXfieldsWriteTranslation',
								'settings[xfields][write][translation]',
								$xfields,
								$cdnhub->config['xfields']['write']['translation']
							),
							'Доп. поле для заполнения перевода видео'
						); ?>

					</div>

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteTranslations',
							'Список переводов сериала',
							CDNHubForm::select(
								'moduleXfieldsWriteTranslations',
								'settings[xfields][write][translations]',
								$xfields,
								$cdnhub->config['xfields']['write']['translations']
							),
							'Доп. поле для заполнения списка всех переводов сериала'
						); ?>

					</div>

					<hr class="vh-separator">

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteSeason',
							'Номер последнего сезона',
							CDNHubForm::select(
								'moduleXfieldsWriteSeason',
								'settings[xfields][write][season]',
								$xfields,
								$cdnhub->config['xfields']['write']['season']
							),
							'Доп. поле для заполнения номера последнего сезона сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteEpisode',
							'Номер последней серии',
							CDNHubForm::select(
								'moduleXfieldsWriteEpisode',
								'settings[xfields][write][episode]',
								$xfields,
								$cdnhub->config['xfields']['write']['episode']
							),
							'Доп. поле для заполнения номера последней серии сериала'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Альтернативный вывод данных</h4>
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteCustomQualitySet',
							'Список своих названий для качества видео',
							'<div>
								<button id="customQualityButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customQualityModal">Настроить свои названия качеств</button>
							</div>',
							'Настройки своих названий для качества видео'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteCustomQuality',
							'Качество видео (с заменой)',
							CDNHubForm::select(
								'moduleXfieldsWriteCustomQuality',
								'settings[xfields][write][custom_quality]',
								$xfields,
								$cdnhub->config['xfields']['write']['custom_quality']
							),
							'Доп. поле для заполнения качества видео с заменой названий'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteCustomTranslationSet',
							'Список своих названий для переводов',
							'<div>
								<button id="customTranslationButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customTranslationModal">Настроить свои названия переводов</button>
							</div>',
							'Настройки своих названий для переводов видео'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteCustomTranslation',
							'Перевод (с заменой)',
							CDNHubForm::select(
								'moduleXfieldsWriteCustomTranslation',
								'settings[xfields][write][custom_translation]',
								$xfields,
								$cdnhub->config['xfields']['write']['custom_translation']
							),
							'Доп. поле для заполнения перевода видео с заменой названий'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteCustomTranslations',
							'Список переводов сериала (с заменой)',
							CDNHubForm::select(
								'moduleXfieldsWriteCustomTranslations',
								'settings[xfields][write][custom_translations]',
								$xfields,
								$cdnhub->config['xfields']['write']['custom_translations']
							),
							'Доп. поле для заполнения списка всех переводов сериала с заменой названий'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteFormatSeasonType',
							'Тип форматирования сезона',
							CDNHubForm::select(
								'moduleXfieldsWriteFormatSeasonType',
								'settings[xfields][write][format_season_type]',
								array(
									0 => '',
									1 => '1 сезон, 2 сезон, 3 сезон',
									2 => '1 сезон, 1-2 сезон, 1-3 сезон',
									3 => '1 сезон, 1,2 сезон, 1,2,3 сезон'
								),
								$cdnhub->config['xfields']['write']['format_season_type']
							),
							'Тип форматирования сезона сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteFormatSeason',
							'Форматированный сезон',
							CDNHubForm::select(
								'moduleXfieldsWriteFormatSeason',
								'settings[xfields][write][format_season]',
								$xfields,
								$cdnhub->config['xfields']['write']['format_season']
							),
							'Доп. поле для заполнения форматированного сезона сериала'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteFormatEpisodeType',
							'Тип форматирования серии',
							CDNHubForm::select(
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
								$cdnhub->config['xfields']['write']['format_episode_type']
							),
							'Тип форматирования серии сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteFormatEpisode',
							'Форматированная серия',
							CDNHubForm::select(
								'moduleXfieldsWriteFormatEpisode',
								'settings[xfields][write][format_episode]',
								$xfields,
								$cdnhub->config['xfields']['write']['format_episode']
							),
							'Доп. поле для заполнения форматированной серии сериала'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Дополнительные поля для вывода данных</h4>

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteTitleRu',
							'Название на Русском',
							CDNHubForm::select(
								'moduleXfieldsWriteTitleRu',
								'settings[xfields][write][title_rus]',
								$xfields,
								$cdnhub->config['xfields']['write']['title_rus']
							),
							'Доп. поле для заполнения названия фильма или сериала на Русском языке'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteTitleOrig',
							'Оригинальное название',
							CDNHubForm::select(
								'moduleXfieldsWriteTitleOrig',
								'settings[xfields][write][title_orig]',
								$xfields,
								$cdnhub->config['xfields']['write']['title_orig']
							),
							'Доп. поле для заполнения Оригинального названия фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteSlogan',
							'Слоган',
							CDNHubForm::select(
								'moduleXfieldsWriteSlogan',
								'settings[xfields][write][slogan]',
								$xfields,
								$cdnhub->config['xfields']['write']['slogan']
							),
							'Доп. поле для заполнения слогана фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteDescription',
							'Описание',
							CDNHubForm::select(
								'moduleXfieldsWriteDescription',
								'settings[xfields][write][description]',
								$xfields,
								$cdnhub->config['xfields']['write']['description']
							),
							'Доп. поле для заполнения Описания фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteYear',
							'Год выпуска',
							CDNHubForm::select(
								'moduleXfieldsWriteYear',
								'settings[xfields][write][year]',
								$xfields,
								$cdnhub->config['xfields']['write']['year']
							),
							'Доп. поле для заполнения Года выпуска фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteDuration',
							'Продолжительность видео',
							CDNHubForm::select(
								'moduleXfieldsWriteDuration',
								'settings[xfields][write][duration]',
								$xfields,
								$cdnhub->config['xfields']['write']['duration']
							),
							'Доп. поле для заполнения Продолжительности видео'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteGenres',
							'Жанры',
							CDNHubForm::select(
								'moduleXfieldsWriteGenres',
								'settings[xfields][write][genres]',
								$xfields,
								$cdnhub->config['xfields']['write']['genres']
							),
							'Доп. поле для заполнения списка Жанров фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteCountries',
							'Страны',
							CDNHubForm::select(
								'moduleXfieldsWriteCountries',
								'settings[xfields][write][countries]',
								$xfields,
								$cdnhub->config['xfields']['write']['countries']
							),
							'Доп. поле для заполнения списка Стран фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWriteAge',
							'Возрастное ограничение',
							CDNHubForm::select(
								'moduleXfieldsWriteAge',
								'settings[xfields][write][age]',
								$xfields,
								$cdnhub->config['xfields']['write']['age']
							),
							'Доп. поле для заполнения Возрастного ограничения фильма или сериала'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleXfieldsWritePoster',
							'Постер',
							CDNHubForm::select(
								'moduleXfieldsWritePoster',
								'settings[xfields][write][poster]',
								$xfields,
								$cdnhub->config['xfields']['write']['poster']
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

						<?php echo CDNHubForm::group(
							'moduleSeoOn',
							'Заполнение СЕО данных',
							CDNHubForm::_switch(
								'moduleSeoOn',
								'settings[seo][on]',
								$cdnhub->config['seo']['on'] ? true : false
							),
							'Включение и выключение заполнения СЕО данных'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleSeoUrl',
							'ЧПУ новости',
							CDNHubForm::text(
								'moduleSeoUrl',
								'settings[seo][url]',
								$cdnhub->config['seo']['url'] ? $cdnhub->config['seo']['url'] : false,
								''
							),
							'Шаблон заполнения ЧПУ новости (переводится в транслит)'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleSeoTitle',
							'Заголовок новости',
							CDNHubForm::text(
								'moduleSeoTitle',
								'settings[seo][title]',
								$cdnhub->config['seo']['title'] ? $cdnhub->config['seo']['title'] : false,
								''
							),
							'Шаблон заполнения заголовока новости'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleSeoMetaTitle',
							'Мета-заголовок новости',
							CDNHubForm::text(
								'moduleSeoMetaTitle',
								'settings[seo][meta][title]',
								$cdnhub->config['seo']['meta']['title'] ? $cdnhub->config['seo']['meta']['title'] : false,
								''
							),
							'Шаблон заполнения мета-заголовока новости'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleSeoMetaDescription',
							'Мета-описание новости',
							CDNHubForm::text(
								'moduleSeoMetaDescription',
								'settings[seo][meta][description]',
								$cdnhub->config['seo']['meta']['description'] ? $cdnhub->config['seo']['meta']['description'] : false,
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

						<?php echo CDNHubForm::group(
							'moduleUpdateType',
							'Способ запуска обновления',
							CDNHubForm::radio(
								'moduleUpdateTypeDefault',
								'settings[update][type]',
								'Стандартное обновление',
								0,
								intval($cdnhub->config['update']['type'])
							) . '<div class="text-muted mb-2">Обновление будет запускаться при открытии страниц сайта с интервалом указанным в настройке "<b>Интервал запуска обновления</b>"</div>' . CDNHubForm::radio(
								'moduleUpdateTypeCron',
								'settings[update][type]',
								'Планировщик задач (<b>cron</b>)',
								1,
								intval($cdnhub->config['update']['type'])
							) . '<div class="text-muted">Обновление будет запускаться по расписанию</div>',
							''
						); ?>

						<?php echo CDNHubForm::group(
							'moduleUpdateInterval',
							'Интервал запуска обновления',
							CDNHubForm::select(
								'moduleUpdateInterval',
								'settings[update][interval]',
								array(
									'30m' => '30 минут',
									'1h' => '1 час',
									'2h' => '2 часа',
									'3h' => '3 часа',
								),
								$cdnhub->config['update']['interval'] ? $cdnhub->config['update']['interval'] : '3h'
							),
							'Интервал запуска обновления'
						); ?>

						<?php echo CDNHubForm::group(
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

						<?php echo CDNHubForm::group(
							'moduleXfieldsNotUpdate',
							'Не обновлять',
							CDNHubForm::select(
								'moduleXfieldsNotUpdate',
								'settings[xfields][npt_update]',
								$not_update_xfields,
								$cdnhub->config['xfields']['npt_update']
							),
							'Доп. поле <b>Переключатель \'Да\' или \'Нет\'</b> для исключения новости из обновления (если <b>Да</b>, новость участвовать в обновлении не будет)'
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Настройки обновления фильмов</h4>

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleUpdateMoviesOn',
							'Обновление фильмов',
							CDNHubForm::_switch(
								'moduleUpdateMoviesOn',
								'settings[update][movies][on]',
								$cdnhub->config['update']['movies']['on'] ? true : false
							),
							'Включение и выключение обновления фильмов'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleUpdateMovies',
							'Дополнительные опции обновления фильмов',
							CDNHubForm::checkbox(
								'moduleUpdateMoviesUp',
								'settings[update][movies][up]',
								'Поднимать новость при выходе лучшего качества видео',
								$cdnhub->config['update']['movies']['up'] ? true : false
							) . CDNHubForm::checkbox(
								'moduleUpdateMoviesAdd',
								'settings[update][movies][add]',
								'Добавлять новость если фильм не найден на сайте<br>(попадает на модерацию)',
								$cdnhub->config['update']['movies']['add'] ? true : false
							),
							''
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Настройки обновления сериалов</h4>

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleUpdateSerialsOn',
							'Обновление сериалов',
							CDNHubForm::_switch(
								'moduleUpdateSerialsOn',
								'settings[update][serials][on]',
								$cdnhub->config['update']['serials']['on'] ? true : false
							),
							'Включение и выключение обновления сериалов'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<!-- <div class="row">

						<?php echo CDNHubForm::group(
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

						<?php echo CDNHubForm::group(
							'moduleUpdateSerials',
							'Дополнительные опции обновления сериалов',
							CDNHubForm::checkbox(
								'moduleUpdateSerialsUp',
								'settings[update][serials][up]',
								'Поднимать новость при выходе новой серии сериала',
								$cdnhub->config['update']['serials']['up'] ? true : false
							) . CDNHubForm::checkbox(
								'moduleUpdateSerialsAdd',
								'settings[update][serials][add]',
								'Добавлять новость если сериал не найден на сайте<br>(попадает на модерацию)',
								$cdnhub->config['update']['serials']['add'] ? true : false
							),
							''
						); ?>

					</div>

					<h4 class="card-header sub-card-header mb-3">Блок обновлений сериалов</h4>

					<div class="alert alert-dismissible alert-primary" style="margin-left:-15px;margin-right:-15px;border-radius:3px;margin-top:5px">
					
						<div>
							<strong>{include file="cdnhub/widgets/updates.php"}</strong>
							&mdash; Вывод блока обвновлений сериалов в шаблоне
						</div>

						<hr class="vh-separator mt-2 mb-2">

						<div>
							Файл шаблона для редактирования блока обновлений сериалов находится по этому пути &mdash; <strong>cdnhub/widgets/updates.tpl</strong>
						</div>

					</div>

					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleSerialsUpdatesOn',
							'Блок обновлений сериалов',
							CDNHubForm::_switch(
								'moduleSerialsUpdatesOn',
								'settings[serials][updates][on]',
								$cdnhub->config['serials']['updates']['on'] ? true : false
							),
							'Включение и выключение вывода блока обновлений сериалов'
						); ?>

					</div>

					<hr class="vh-separator">
					
					<div class="row">

						<?php echo CDNHubForm::group(
							'moduleSerialsUpdatesDays',
							'Кол-во дней',
							CDNHubForm::text(
								'moduleSerialsUpdatesDays',
								'settings[serials][updates][days]',
								$cdnhub->config['serials']['updates']['days'] ? $cdnhub->config['serials']['updates']['days'] : false,
								'7'
							),
							'Кол-во дней за которое выводить обновления в блоке<br>(по умолчанию последние <b>7</b> дней)'
						); ?>

						<?php echo CDNHubForm::group(
							'moduleSerialsUpdatesDayItems',
							'Макс. кол-во записей',
							CDNHubForm::text(
								'moduleSerialsUpdatesDayItems',
								'settings[serials][updates][items]',
								$cdnhub->config['serials']['updates']['items'] ? $cdnhub->config['serials']['updates']['items'] : false,
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
					<?php if ($cdnhub->config['custom']['qualities']) foreach ($cdnhub->config['custom']['qualities'] as $pattern => $replacement) { ?>
						<div class="form-inline custom-quality">
							<input type="text" class="form-control custom-quality-from" placeholder="Название из базы" value="<?php echo cdnhub_encode($pattern); ?>">
							<input type="text" class="form-control custom-quality-to" placeholder="Своё название" value="<?php echo cdnhub_encode($replacement); ?>">
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
					<?php if ($cdnhub->config['custom']['translations']) foreach ($cdnhub->config['custom']['translations'] as $pattern => $replacement) { ?>
						<div class="form-inline custom-translation">
							<input type="text" class="form-control custom-translation-from" placeholder="Название из базы" value="<?php echo cdnhub_encode($pattern); ?>">
							<input type="text" class="form-control custom-translation-to" placeholder="Своё название" value="<?php echo cdnhub_encode($replacement); ?>">
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
							if ($cdnhub->config['update']['serials']['priority']) foreach ($cdnhub->config['update']['serials']['priority'] as $key) {
								if (!$cdnhub->config['translations'][$key])
									continue;
								else
									$translation = $cdnhub->config['translations'][$key];
						?>
							<div id="serialsTranslation<?=$key?>" class="sortable sortable-selected btn btn-outline-info" data-id="<?=$key?>"><?=$translation?><a href="javascript:void(0)" data-id="<?=$key?>" title="Удалить перевод"><i class="fas fa-times"></i></a></div>
						<?php } ?>
					</div>

					<div id="serialsNoPriorityContainer" class="sort-container">
						<?php
							if ($cdnhub->config['translations']) foreach ($cdnhub->config['translations'] as $key => $translation) {
								if (in_array($key, $cdnhub->config['update']['serials']['priority']))
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