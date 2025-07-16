<?php

$baseUrl = $PHP_SELF . '?mod=cdnhub';

require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/../classes/CDNHubForm.php';

if (!$cdnhub->config['on'] || !$cdnhub->config['api']['token'])
	return false;

$search = array(
	'title' => '#vhSearchTitle',
	'kinopoisk_id' => $cdnhub->config['xfields']['search']['kinopoisk_id'] ? "#xf_{$cdnhub->config['xfields']['search']['kinopoisk_id']}" : '',
	'imdb_id' => $cdnhub->config['xfields']['search']['imdb_id'] ? "#xf_{$cdnhub->config['xfields']['search']['imdb_id']}" : ''
);

$write = array(
	'source' => $cdnhub->config['xfields']['write']['source'] ? "#xf_{$cdnhub->config['xfields']['write']['source']}" : '',

	'quality' => $cdnhub->config['xfields']['write']['quality'] ? "#xf_{$cdnhub->config['xfields']['write']['quality']}" : '',
	'translation' => $cdnhub->config['xfields']['write']['translation'] ? "#xf_{$cdnhub->config['xfields']['write']['translation']}" : '',
	'translations' => $cdnhub->config['xfields']['write']['translations'] ? "#xf_{$cdnhub->config['xfields']['write']['translations']}" : '',
	'custom_quality' => $cdnhub->config['xfields']['write']['custom_quality'] ? "#xf_{$cdnhub->config['xfields']['write']['custom_quality']}" : '',
	'custom_translation' => $cdnhub->config['xfields']['write']['custom_translation'] ? "#xf_{$cdnhub->config['xfields']['write']['custom_translation']}" : '',
	'custom_translations' => $cdnhub->config['xfields']['write']['custom_translations'] ? "#xf_{$cdnhub->config['xfields']['write']['custom_translations']}" : '',
	'season' => $cdnhub->config['xfields']['write']['season'] ? "#xf_{$cdnhub->config['xfields']['write']['season']}" : '',
	'episode' => $cdnhub->config['xfields']['write']['episode'] ? "#xf_{$cdnhub->config['xfields']['write']['episode']}" : '',
	'format_season' => $cdnhub->config['xfields']['write']['format_season'] ? "#xf_{$cdnhub->config['xfields']['write']['format_season']}" : '',
	'format_episode' => $cdnhub->config['xfields']['write']['format_episode'] ? "#xf_{$cdnhub->config['xfields']['write']['format_episode']}" : '',

	'title_rus' => $cdnhub->config['xfields']['write']['title_rus'] ? "#xf_{$cdnhub->config['xfields']['write']['title_rus']}" : '',
	'title_orig' => $cdnhub->config['xfields']['write']['title_orig'] ? "#xf_{$cdnhub->config['xfields']['write']['title_orig']}" : '',
	'slogan' => $cdnhub->config['xfields']['write']['slogan'] ? "#xf_{$cdnhub->config['xfields']['write']['slogan']}" : '',
	'description' => $cdnhub->config['xfields']['write']['description'] ? "#xf_{$cdnhub->config['xfields']['write']['description']}" : '',
	'year' => $cdnhub->config['xfields']['write']['year'] ? "#xf_{$cdnhub->config['xfields']['write']['year']}" : '',
	'duration' => $cdnhub->config['xfields']['write']['duration'] ? "#xf_{$cdnhub->config['xfields']['write']['duration']}" : '',
	'genres' => $cdnhub->config['xfields']['write']['genres'] ? "#xf_{$cdnhub->config['xfields']['write']['genres']}" : '',
	'countries' => $cdnhub->config['xfields']['write']['countries'] ? "#xf_{$cdnhub->config['xfields']['write']['countries']}" : '',
	'age' => $cdnhub->config['xfields']['write']['age'] ? "#xf_{$cdnhub->config['xfields']['write']['age']}" : '',
	'poster' => $cdnhub->config['xfields']['write']['poster'] ? "#xf_{$cdnhub->config['xfields']['write']['poster']}" : '',
);

$seo = array(
	'on' => $cdnhub->config['seo']['on'] ? 'true' : 'false',
	'url' => 'input[name="alt_name"]',
	'title' => '#title',
	'meta_title' => 'input[name="meta_title"]',
	'meta_description' => '#autodescr'
);

$output .= "<div class=\"form-group\">
	<label class=\"control-label col-sm-2\">CDNHub:</label>
	<div class=\"col-sm-10\">

	<link href=\"/cdnhub/admin/assets/css/search.css?v=14\" rel=\"stylesheet\">

	<div class=\"vh\">
		
		<input type=\"text\" id=\"vhSearchTitle\" class=\"vh-form-control\" placeholder=\"Название видео\" title=\"Заполните для поиска видео в базе по названию\">

		<button type=\"button\" id=\"vhSearch\" class=\"vh-btn vh-btn-primary\">Найти в базе CDNHub</button>
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

						" . CDNHubForm::group(
							'moduleReplacementXfields',
							'',
							CDNHubForm::checkbox(
								'xfeildSource',
								'xfields[source]',
								'Источник видео',
								true,
								$write['source'] ? false : true
							) . CDNHubForm::checkbox(
								'xfeildQuality',
								'xfields[quality]',
								'Качество видео',
								true,
								$write['quality'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldTranslation',
								'xfields[translation]',
								'Перевод',
								true,
								$write['translation'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldTranslations',
								'xfields[translations]',
								'Список переводов сериала',
								true,
								$write['translations'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldSeason',
								'xfields[season]',
								'Номер последнего сезона',
								true,
								$write['season'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldEpisode',
								'xfields[episode]',
								'Номер последней серии',
								true,
								$write['episode'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldCustomQuality',
								'xfields[custom_quality]',
								'Качетсво видео (с заменой)',
								true,
								$write['custom_quality'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldCustomTranslation',
								'xfields[custom_translation]',
								'Перевод (с заменой)',
								true,
								$write['custom_translation'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldCustomTranslations',
								'xfields[custom_translations]',
								'Список переводов сериала (с заменой)',
								true,
								$write['custom_translations'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldFormatSeason',
								'xfields[format_season]',
								'Форматированный сезон',
								true,
								$write['format_season'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldFormatEpisode',
								'xfields[format_episode]',
								'Форматированная серия',
								true,
								$write['format_episode'] ? false : true
							),
							''
						) . "

						" . CDNHubForm::group(
							'moduleReplacementXfieldsAdditional',
							'',
							CDNHubForm::checkbox(
								'xfieldTitleRus',
								'xfields[title_rus]',
								'Название на Русском',
								true,
								$write['title_rus'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldTitleOrig',
								'xfields[title_orig]',
								'Оригинальное название',
								true,
								$write['title_orig'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldSlogan',
								'xfields[slogan]',
								'Слоган',
								true,
								$write['slogan'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldDescription',
								'xfields[description]',
								'Описание',
								true,
								$write['description'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldYear',
								'xfields[year]',
								'Год выпуска',
								true,
								$write['year'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldDuration',
								'xfields[duration]',
								'Продолжительность видео',
								true,
								$write['duration'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldGenres',
								'xfields[genres]',
								'Жанры',
								true,
								$write['genres'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldCountries',
								'xfields[countries]',
								'Страны',
								true,
								$write['countries'] ? false : true
							) . CDNHubForm::checkbox(
								'xfieldAge',
								'xfields[age]',
								'Возрастное ограничение',
								true,
								$write['age'] ? false : true
							) . CDNHubForm::checkbox(
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

						" . CDNHubForm::group(
							'moduleReplacementXfieldsAdditional',
							'',
							CDNHubForm::checkbox(
								'seoUrl',
								'seo[url]',
								'ЧПУ новости',
								true,
								$cdnhub->config['seo']['url'] ? false : true
							) . CDNHubForm::checkbox(
								'seoTitle',
								'seo[title]',
								'Заголовок новости',
								true,
								$cdnhub->config['seo']['title'] ? false : true
							) . CDNHubForm::checkbox(
								'seoMetaTitle',
								'seo[meta_title]',
								'Мета-заголовок новости',
								true,
								$cdnhub->config['seo']['meta']['title'] ? false : true
							) . CDNHubForm::checkbox(
								'seoMetaDescription',
								'seo[meta_description]',
								'Мета-описание новости',
								true,
								$cdnhub->config['seo']['meta']['description'] ? false : true
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

	//-->
</script>
<script src=\"/cdnhub/admin/assets/js/bootstrap.bundle.min.js\"></script>
<script src=\"/cdnhub/admin/assets/js/search.js?v=24\"></script>

</div>
</div>";