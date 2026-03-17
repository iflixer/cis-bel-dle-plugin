<?php

$baseUrl = $PHP_SELF . '?mod=flixcdn';

require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/../classes/FlixCDNForm.php';

if (!$flixcdn->config['on'] || !$flixcdn->config['api']['token'])
	return false;

$search = array(
	'title' => '#vhSearchTitle',
	'kinopoisk_id' => $flixcdn->config['xfields']['search']['kinopoisk_id'] ? "#xf_{$flixcdn->config['xfields']['search']['kinopoisk_id']}" : '',
	'imdb_id' => $flixcdn->config['xfields']['search']['imdb_id'] ? "#xf_{$flixcdn->config['xfields']['search']['imdb_id']}" : ''
);

$write = array(
	'source' => $flixcdn->config['xfields']['write']['source'] ? "#xf_{$flixcdn->config['xfields']['write']['source']}" : '',

	'quality' => $flixcdn->config['xfields']['write']['quality'] ? "#xf_{$flixcdn->config['xfields']['write']['quality']}" : '',
	'translation' => $flixcdn->config['xfields']['write']['translation'] ? "#xf_{$flixcdn->config['xfields']['write']['translation']}" : '',
	'translations' => $flixcdn->config['xfields']['write']['translations'] ? "#xf_{$flixcdn->config['xfields']['write']['translations']}" : '',
	'custom_quality' => $flixcdn->config['xfields']['write']['custom_quality'] ? "#xf_{$flixcdn->config['xfields']['write']['custom_quality']}" : '',
	'custom_translation' => $flixcdn->config['xfields']['write']['custom_translation'] ? "#xf_{$flixcdn->config['xfields']['write']['custom_translation']}" : '',
	'custom_translations' => $flixcdn->config['xfields']['write']['custom_translations'] ? "#xf_{$flixcdn->config['xfields']['write']['custom_translations']}" : '',
	'season' => $flixcdn->config['xfields']['write']['season'] ? "#xf_{$flixcdn->config['xfields']['write']['season']}" : '',
	'episode' => $flixcdn->config['xfields']['write']['episode'] ? "#xf_{$flixcdn->config['xfields']['write']['episode']}" : '',
	'format_season' => $flixcdn->config['xfields']['write']['format_season'] ? "#xf_{$flixcdn->config['xfields']['write']['format_season']}" : '',
	'format_episode' => $flixcdn->config['xfields']['write']['format_episode'] ? "#xf_{$flixcdn->config['xfields']['write']['format_episode']}" : '',

	'title_rus' => $flixcdn->config['xfields']['write']['title_rus'] ? "#xf_{$flixcdn->config['xfields']['write']['title_rus']}" : '',
	'title_orig' => $flixcdn->config['xfields']['write']['title_orig'] ? "#xf_{$flixcdn->config['xfields']['write']['title_orig']}" : '',
	'slogan' => $flixcdn->config['xfields']['write']['slogan'] ? "#xf_{$flixcdn->config['xfields']['write']['slogan']}" : '',
	'description' => $flixcdn->config['xfields']['write']['description'] ? "#xf_{$flixcdn->config['xfields']['write']['description']}" : '',
	'year' => $flixcdn->config['xfields']['write']['year'] ? "#xf_{$flixcdn->config['xfields']['write']['year']}" : '',
	'duration' => $flixcdn->config['xfields']['write']['duration'] ? "#xf_{$flixcdn->config['xfields']['write']['duration']}" : '',
	'genres' => ($flixcdn->config['genres_storage'] === 'categories') ? 'select[name="category[]"]' : (($flixcdn->config['xfields']['write']['genres_custom']) ? "#xf_{$flixcdn->config['xfields']['write']['genres_custom']}" : ''),
	'countries' => $flixcdn->config['xfields']['write']['countries'] ? "#xf_{$flixcdn->config['xfields']['write']['countries']}" : '',
	'age' => $flixcdn->config['xfields']['write']['age'] ? "#xf_{$flixcdn->config['xfields']['write']['age']}" : '',
	'poster' => $flixcdn->config['xfields']['write']['poster'] ? "#xf_{$flixcdn->config['xfields']['write']['poster']}" : '',
);

$seo = array(
	'on' => $flixcdn->config['seo']['on'] ? 'true' : 'false',
	'url' => 'input[name="alt_name"]',
	'title' => '#title',
	'meta_title' => 'input[name="meta_title"]',
	'meta_description' => '#autodescr'
);

$output .= "<div class=\"form-group\">
	<label class=\"control-label col-sm-2\">FlixCDN:</label>
	<div class=\"col-sm-10\">

	<link href=\"/flixcdn/admin/assets/css/search.css?v=14\" rel=\"stylesheet\">

	<div class=\"vh\">
		
		<input type=\"text\" id=\"vhSearchTitle\" class=\"vh-form-control\" placeholder=\"Название видео\" title=\"Заполните для поиска видео в базе по названию\">

		<button type=\"button\" id=\"vhSearch\" class=\"vh-btn vh-btn-primary\">Найти в базе FlixCDN</button>
		<button type=\"button\" id=\"vhClearSearch\" class=\"vh-btn vh-btn-danger\" style=\"display:none\">Очитстить поиск</button>
		<button type=\"button\" id=\"vhClearSource\" class=\"vh-btn vh-btn-warning\" style=\"display:none\">Удалить ссылку на источник</button>

		<div id=\"vhNotFound\" style=\"display:none\">
			По вашему запросу в базе ничего не найдено
		</div>

		<div id=\"vhSearchResults\" style=\"display:none\"></div>

	</div>

	<div class=\"modal vh-modal\" tabindex=\"-1\" id=\"vhWriteModal\" aria-labelledby=\"vhWriteModalLabel\" aria-hidden=\"true\">
		<div class=\"modal-dialog modal-lg\">
			<div class=\"modal-content\">
				<div class=\"modal-header\">
					<h5 class=\"modal-title\" id=\"vhWriteModalTitle\">Заполнение полей</h5>
					<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
				</div>
				<div class=\"modal-body\">
					
					<h4 class=\"card-header sub-card-header mb-3\"><label><input class=\"checkAll\" type=\"checkbox\" value=\"1\" checked> Выберите поля которые нужно заполнять</label></h4>
					
					<div class=\"row\" style=\"padding-left:15px;padding-right:15px\">

						" . FlixCDNForm::group(
							'moduleReplacementXfields',
							'',
							FlixCDNForm::checkbox(
								'xfeildSource',
								'xfields[source]',
								'Источник видео',
								true,
								$write['source'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfeildQuality',
								'xfields[quality]',
								'Качество видео',
								true,
								$write['quality'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldTranslation',
								'xfields[translation]',
								'Перевод',
								true,
								$write['translation'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldTranslations',
								'xfields[translations]',
								'Список переводов сериала',
								true,
								$write['translations'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldSeason',
								'xfields[season]',
								'Номер последнего сезона',
								true,
								$write['season'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldEpisode',
								'xfields[episode]',
								'Номер последней серии',
								true,
								$write['episode'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldCustomQuality',
								'xfields[custom_quality]',
								'Качетсво видео (с заменой)',
								true,
								$write['custom_quality'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldCustomTranslation',
								'xfields[custom_translation]',
								'Перевод (с заменой)',
								true,
								$write['custom_translation'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldCustomTranslations',
								'xfields[custom_translations]',
								'Список переводов сериала (с заменой)',
								true,
								$write['custom_translations'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldFormatSeason',
								'xfields[format_season]',
								'Форматированный сезон',
								true,
								$write['format_season'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldFormatEpisode',
								'xfields[format_episode]',
								'Форматированная серия',
								true,
								$write['format_episode'] ? false : true
							),
							''
						) . "

						" . FlixCDNForm::group(
							'moduleReplacementXfieldsAdditional',
							'',
							FlixCDNForm::checkbox(
								'xfieldTitleRus',
								'xfields[title_rus]',
								'Название на Русском',
								true,
								$write['title_rus'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldTitleOrig',
								'xfields[title_orig]',
								'Оригинальное название',
								true,
								$write['title_orig'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldSlogan',
								'xfields[slogan]',
								'Слоган',
								true,
								$write['slogan'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldDescription',
								'xfields[description]',
								'Описание',
								true,
								$write['description'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldYear',
								'xfields[year]',
								'Год выпуска',
								true,
								$write['year'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldDuration',
								'xfields[duration]',
								'Продолжительность видео',
								true,
								$write['duration'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldGenres',
								'xfields[genres]',
								'Жанры',
								true,
								$write['genres'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldCountries',
								'xfields[countries]',
								'Страны',
								true,
								$write['countries'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldAge',
								'xfields[age]',
								'Возрастное ограничение',
								true,
								$write['age'] ? false : true
							) . FlixCDNForm::checkbox(
								'xfieldPoster',
								'xfields[poster]',
								'Постер',
								true,
								$write['poster'] ? false : true
							),
							''
						) . "

					</div>
						
					<h4 class=\"card-header sub-card-header mb-3\"><label><input class=\"checkAll\" type=\"checkbox\" value=\"1\" checked> Укажите какие СЕО данные нужно заполнять</label></h4>
						
					<div class=\"row\" style=\"padding-left:15px;padding-right:15px\">

						" . FlixCDNForm::group(
							'moduleReplacementXfieldsAdditional',
							'',
							FlixCDNForm::checkbox(
								'seoUrl',
								'seo[url]',
								'ЧПУ новости',
								true,
								$flixcdn->config['seo']['url'] ? false : true
							) . FlixCDNForm::checkbox(
								'seoTitle',
								'seo[title]',
								'Заголовок новости',
								true,
								$flixcdn->config['seo']['title'] ? false : true
							) . FlixCDNForm::checkbox(
								'seoMetaTitle',
								'seo[meta_title]',
								'Мета-заголовок новости',
								true,
								$flixcdn->config['seo']['meta']['title'] ? false : true
							) . FlixCDNForm::checkbox(
								'seoMetaDescription',
								'seo[meta_description]',
								'Мета-описание новости',
								true,
								$flixcdn->config['seo']['meta']['description'] ? false : true
							),
							''
						) . "

					</div>

				</div>
				<div class=\"modal-footer\">
					<button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Закрыть</button>
					<button type=\"button\" class=\"btn btn-primary\" style=\"margin-bottom:0.25rem\" id=\"vhWrtieSelectedFields\">Заполнить поля</button>
				</div>
			</div>
		</div>
	</div>

	<script>
	<!--

		var vhBaseUrl = '{$baseUrl}';

		var vh = {};

		vh.search = {};
		vh.search.title = '{$search['title']}';
		vh.search.kinopoisk_id = '{$search['kinopoisk_id']}';
		vh.search.imdb_id = '{$search['imdb_id']}';

		vh.write = {};

		vh.write.source = '{$write['source']}';

		vh.write.quality = '{$write['quality']}';
		vh.write.translation = '{$write['translation']}';
		vh.write.translations = '{$write['translations']}';
		vh.write.custom_quality = '{$write['custom_quality']}';
		vh.write.custom_translation = '{$write['custom_translation']}';
		vh.write.custom_translations = '{$write['custom_translations']}';
		vh.write.season = '{$write['season']}';
		vh.write.episode = '{$write['episode']}';
		vh.write.format_season = '{$write['format_season']}';
		vh.write.format_episode = '{$write['format_episode']}';

		vh.write.kinopoisk_id = '{$search['kinopoisk_id']}';
		vh.write.imdb_id = '{$search['imdb_id']}';

		vh.write.title_rus = '{$write['title_rus']}';
		vh.write.title_orig = '{$write['title_orig']}';
		vh.write.slogan = '{$write['slogan']}';
		vh.write.description = '{$write['description']}';
		vh.write.year = '{$write['year']}';
		vh.write.duration = '{$write['duration']}';
		vh.write.genres = '{$write['genres']}';
		vh.write.countries = '{$write['countries']}';
		vh.write.age = '{$write['age']}';
		vh.write.poster = '{$write['poster']}';

		vh.seo = {};
		vh.seo.on = {$seo['on']};
		vh.seo.url = '{$seo['url']}';
		vh.seo.title = '{$seo['title']}';
		vh.seo.meta_title = '{$seo['meta_title']}';
		vh.seo.meta_description = '{$seo['meta_description']}';

		vh.mapping = {};
		vh.mapping.storage_mode = '{$flixcdn->config['genres_storage']}';
		vh.mapping.genres_mappings = {};
		
		if (vh.mapping.storage_mode === 'categories') {
			$.ajax({
				url: vhBaseUrl + '&action=load_mapping_data',
				dataType: 'json',
				cache: false,
				async: false,
				success: function(data) {
					vh.mapping.genres_mappings = data.genres_mappings || {};
				}
			});
		}

	//-->
</script>
<script src=\"/flixcdn/admin/assets/js/bootstrap.bundle.min.js\"></script>
<script src=\"/flixcdn/admin/assets/js/search.js?v=24\"></script>

</div>
</div>";