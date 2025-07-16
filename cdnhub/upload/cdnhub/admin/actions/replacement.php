<?php

$pageTitle = 'CDNHub - Массовое проставление данных';

include dirname(__FILE__) . '/header.php';

?>

<form id="replacementForm" action="">

	<div class="card bg-secondary mb-3">
  <div class="card-header">Массовое проставление данных</div>
  <div class="card-body">
			
				<div class="row">
							
					<?php echo CDNHubForm::group(
						'fields',
						'Поля для поиска',
						'<div class="row"><div class="col">' . CDNHubForm::checkbox(
							'kinopoisk',
							'replacement[search][kinopoisk_id]',
							'Kinopoisk&nbsp;ID',
							false
						) . '</div><div class="col">' . CDNHubForm::checkbox(
							'imdb',
							'replacement[search][imdb_id]',
							'IMDb&nbsp;ID',
							false
						) . '</div><div class="col"></div><div class="col"></div></div>',
						'Поля для поиска видео в базе'
					); ?>

					<?php echo CDNHubForm::group(
						'rewrite',
						'Перезаписывать',
						CDNHubForm::_switch(
							'rewrite',
							'replacement[rewrite]',
							false
						),
						'Перезаписывать данные если они были заполнены ранее'
					); ?>

				</div>

				<hr class="vh-separator">
					
				<div class="row">

					<?php
						$xfieldsaction = "categoryfilter";
						include ENGINE_DIR . '/inc/xfields.php';
						echo $categoryfilter;

						$categories_list = CategoryNewsSelection(0, 0);
					?>

					<?php echo CDNHubForm::group(
						'category',
						'Категории',
						"<select data-placeholder=\"Выберите категории ...\" name=\"replacement[category][]\" id=\"category\" onchange=\"onCategoryChange(this)\" class=\"categoryselect\" multiple style=\"width:100%;max-width:350px\">
							{$categories_list}
						</select>
						<div class=\"form-check\" style=\"float:right;margin-top:-30px;position:relative\" title=\"Исключить выбранные категории\">
							<input type=\"checkbox\" name=\"replacement[category_inverse]\" value=\"1\" class=\"form-check-input\" id=\"categoryInverse\">
							<label class=\"form-check-label\" for=\"categoryInverse\"></label>
						</div>",
						'Категории новостей'
					); ?>

					<?php echo CDNHubForm::group(
						'status',
						'Статус',
						CDNHubForm::select(
							'status',
							'replacement[status]',
							array(
								0 => 'Все',
								1 => 'Опубликованные',
								2 => 'На модерации',
							),
							''
						),
						'Статус новостей'
					); ?>

				</div>

				<hr class="vh-separator">
					
				<div class="row">

					<?php echo CDNHubForm::group(
						'threads',
						'Потоки',
						CDNHubForm::select(
							'threads',
							'replacement[threads]',
							array(
								1 => 1,
								2 => 2,
								3 => 3,
								5 => 5,
								6 => 6,
								7 => 7,
								8 => 8,
								9 => 9,
								10 => 10,
							),
							3
						),
						'Выставите оптимальное кол-во одновременных потоков</i>'
					); ?>

					<?php echo CDNHubForm::group(
						'interval',
						'Интервал',
						CDNHubForm::select(
							'interval',
							'replacement[interval]',
							array(
								/*0 => '0 мс',
								100 => '100 мс',
								200 => '200 мс',
								300 => '300 мс',
								500 => '500 мс',*/
								1000 => '1 сек',
								2000 => '2 сек',
								3000 => '3 сек',
							),
							1000
						),
						'Выставите оптимальный интервал межу запуском потоков</i>'
					); ?>

				</div>

				<h4 class="card-header sub-card-header mb-3">
					<label>
						<div class="form-check my-1 mr-sm-2">
							<input type="checkbox" value="1" class="form-check-input checkAll" style="margin-top:1px" checked>
						</div>
						<span style="padding-left:23px">Выберите поля которые нужно заполнять</span>
					</label>
				</h4>
					
				<div class="row">

					<?php echo CDNHubForm::group(
						'moduleReplacementXfields',
						'',
						CDNHubForm::checkbox(
							'replacementXfeildsSource',
							'replacement[xfields][source]',
							'Источник видео',
							true,
							$cdnhub->config['xfields']['write']['source'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsQuality',
							'replacement[xfields][quality]',
							'Качество видео',
							true,
							$cdnhub->config['xfields']['write']['quality'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsTranslation',
							'replacement[xfields][translation]',
							'Перевод',
							true,
							$cdnhub->config['xfields']['write']['translation'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsTranslations',
							'replacement[xfields][translations]',
							'Список переводов сериала',
							true,
							$cdnhub->config['xfields']['write']['translations'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsSeason',
							'replacement[xfields][season]',
							'Номер последнего сезона',
							true,
							$cdnhub->config['xfields']['write']['season'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsEpisode',
							'replacement[xfields][episode]',
							'Номер последней серии',
							true,
							$cdnhub->config['xfields']['write']['episode'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsCustomQuality',
							'replacement[xfields][custom_quality]',
							'Качетсво видео (с заменой)',
							true,
							$cdnhub->config['xfields']['write']['custom_quality'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsCustomTranslation',
							'replacement[xfields][custom_translation]',
							'Перевод (с заменой)',
							true,
							$cdnhub->config['xfields']['write']['custom_translation'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsCustomTranslations',
							'replacement[xfields][custom_translations]',
							'Список переводов сериала (с заменой)',
							true,
							$cdnhub->config['xfields']['write']['custom_translations'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsFormatSeason',
							'replacement[xfields][format_season]',
							'Форматированный сезон',
							true,
							$cdnhub->config['xfields']['write']['format_season'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsFormatEpisode',
							'replacement[xfields][format_episode]',
							'Форматированная серия',
							true,
							$cdnhub->config['xfields']['write']['format_episode'] ? false : true
						),
						''
					); ?>

					<?php echo CDNHubForm::group(
						'moduleReplacementXfieldsAdditional',
						'',
						CDNHubForm::checkbox(
							'replacementXfeildsTitleRus',
							'replacement[xfields][title_rus]',
							'Название на Русском',
							true,
							$cdnhub->config['xfields']['write']['title_rus'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsTitleOrig',
							'replacement[xfields][title_orig]',
							'Оригинальное название',
							true,
							$cdnhub->config['xfields']['write']['title_orig'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsSlogan',
							'replacement[xfields][slogan]',
							'Слоган',
							true,
							$cdnhub->config['xfields']['write']['slogan'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsDescription',
							'replacement[xfields][description]',
							'Описание',
							true,
							$cdnhub->config['xfields']['write']['description'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsYear',
							'replacement[xfields][year]',
							'Год выпуска',
							true,
							$cdnhub->config['xfields']['write']['year'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsDuration',
							'replacement[xfields][duration]',
							'Продолжительность видео',
							true,
							$cdnhub->config['xfields']['write']['duration'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsGenres',
							'replacement[xfields][genres]',
							'Жанры',
							true,
							$cdnhub->config['xfields']['write']['genres'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsCountries',
							'replacement[xfields][countries]',
							'Страны',
							true,
							$cdnhub->config['xfields']['write']['countries'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsAge',
							'replacement[xfields][age]',
							'Возрастное ограничение',
							true,
							$cdnhub->config['xfields']['write']['age'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementXfeildsPoster',
							'replacement[xfields][poster]',
							'Постер',
							true,
							$cdnhub->config['xfields']['write']['poster'] ? false : true
						),
						''
					); ?>

				</div>
					
				<h4 class="card-header sub-card-header mb-3">
					<label>
						<div class="form-check my-1 mr-sm-2">
							<input type="checkbox" value="1" class="form-check-input checkAll" style="margin-top:1px" checked>
						</div>
						<span style="padding-left:23px">Укажите какие СЕО данные нужно заполнять</span>
					</label>
				</h4>
					
				<div class="row">

					<?php echo CDNHubForm::group(
						'moduleReplacementXfieldsAdditional',
						'',
						CDNHubForm::checkbox(
							'replacementSeoUrl',
							'replacement[seo][url]',
							'ЧПУ новости',
							true,
							$cdnhub->config['seo']['url'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementSeoTitle',
							'replacement[seo][title]',
							'Заголовок новости',
							true,
							$cdnhub->config['seo']['title'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementSeoMetaTitle',
							'replacement[seo][meta_title]',
							'Мета-заголовок новости',
							true,
							$cdnhub->config['seo']['meta']['title'] ? false : true
						) . CDNHubForm::checkbox(
							'replacementSeoMetaDescription',
							'replacement[seo][meta_description]',
							'Мета-описание новости',
							true,
							$cdnhub->config['seo']['meta']['description'] ? false : true
						),
						''
					); ?>

				</div>

			</div>
		</div>

	

</form>

<div class="ks-status-table">
	<table class="table table-hover" id="replacementStatus" data-status="abort" data-post-id="0">
		<thead>
			<tr>
				<th scope="col">Осталось</th>
				<th scope="col">Успешно</th>
				<th scope="col">Существует</th>
				<th scope="col">Не&nbsp;найдено</th>
			</tr>
		</thead>
		<tbody>
			<tr class="table-dark">
				<td><span id="replacementCountContinue" class="badge bg-primary vh-status">0</span></td>
				<td><span id="replacementCountSuccess" class="badge bg-success vh-status">0</span></td>
				<td><span id="replacementCountExist" class="badge bg-warning vh-status">0</span></td>
				<td><span id="replacementCountNotFound" class="badge bg-danger vh-status">0</span></td>
			</tr>
		</tbody>
	</table>
</div>

<button id="replacementStart" type="button" class="btn btn-success">Начать</button>
<button id="replacementStop" style="display:none" type="button" class="btn btn-light">Остановить</button>
<button id="replacementAbort" style="display:none" type="button" class="btn btn-danger">Отменить</button>

<div class="mb-3"></div>

<?php

include dirname(__FILE__) . '/footer.php';