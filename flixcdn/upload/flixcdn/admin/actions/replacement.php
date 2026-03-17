<?php

$pageTitle = 'FlixCDN - Массовое проставление данных';

include dirname(__FILE__) . '/header.php';

?>

<form id="replacementForm" action="">

	<div class="card bg-secondary mb-3">
  <div class="card-header">Массовое проставление данных</div>
  <div class="card-body">
			
				<div class="row">
							
					<?php echo FlixCDNForm::group(
						'fields',
						'Поля для поиска',
						'<div class="row"><div class="col">' . FlixCDNForm::checkbox(
							'kinopoisk',
							'replacement[search][kinopoisk_id]',
							'Kinopoisk&nbsp;ID',
							false
						) . '</div><div class="col">' . FlixCDNForm::checkbox(
							'imdb',
							'replacement[search][imdb_id]',
							'IMDb&nbsp;ID',
							false
						) . '</div><div class="col"></div><div class="col"></div></div>',
						'Поля для поиска видео в базе'
					); ?>

					<?php echo FlixCDNForm::group(
						'rewrite',
						'Перезаписывать',
						FlixCDNForm::_switch(
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

					<?php echo FlixCDNForm::group(
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

					<?php echo FlixCDNForm::group(
						'status',
						'Статус',
						FlixCDNForm::select(
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

					<?php echo FlixCDNForm::group(
						'threads',
						'Потоки',
						FlixCDNForm::select(
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

					<?php echo FlixCDNForm::group(
						'interval',
						'Интервал',
						FlixCDNForm::select(
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

					<?php echo FlixCDNForm::group(
						'moduleReplacementXfields',
						'',
						FlixCDNForm::checkbox(
							'replacementXfeildsSource',
							'replacement[xfields][source]',
							'Источник видео',
							true,
							$flixcdn->config['xfields']['write']['source'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsQuality',
							'replacement[xfields][quality]',
							'Качество видео',
							true,
							$flixcdn->config['xfields']['write']['quality'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsTranslation',
							'replacement[xfields][translation]',
							'Перевод',
							true,
							$flixcdn->config['xfields']['write']['translation'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsTranslations',
							'replacement[xfields][translations]',
							'Список переводов сериала',
							true,
							$flixcdn->config['xfields']['write']['translations'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsSeason',
							'replacement[xfields][season]',
							'Номер последнего сезона',
							true,
							$flixcdn->config['xfields']['write']['season'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsEpisode',
							'replacement[xfields][episode]',
							'Номер последней серии',
							true,
							$flixcdn->config['xfields']['write']['episode'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsCustomQuality',
							'replacement[xfields][custom_quality]',
							'Качетсво видео (с заменой)',
							true,
							$flixcdn->config['xfields']['write']['custom_quality'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsCustomTranslation',
							'replacement[xfields][custom_translation]',
							'Перевод (с заменой)',
							true,
							$flixcdn->config['xfields']['write']['custom_translation'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsCustomTranslations',
							'replacement[xfields][custom_translations]',
							'Список переводов сериала (с заменой)',
							true,
							$flixcdn->config['xfields']['write']['custom_translations'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsFormatSeason',
							'replacement[xfields][format_season]',
							'Форматированный сезон',
							true,
							$flixcdn->config['xfields']['write']['format_season'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsFormatEpisode',
							'replacement[xfields][format_episode]',
							'Форматированная серия',
							true,
							$flixcdn->config['xfields']['write']['format_episode'] ? false : true
						),
						''
					); ?>

					<?php echo FlixCDNForm::group(
						'moduleReplacementXfieldsAdditional',
						'',
						FlixCDNForm::checkbox(
							'replacementXfeildsTitleRus',
							'replacement[xfields][title_rus]',
							'Название на Русском',
							true,
							$flixcdn->config['xfields']['write']['title_rus'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsTitleOrig',
							'replacement[xfields][title_orig]',
							'Оригинальное название',
							true,
							$flixcdn->config['xfields']['write']['title_orig'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsSlogan',
							'replacement[xfields][slogan]',
							'Слоган',
							true,
							$flixcdn->config['xfields']['write']['slogan'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsDescription',
							'replacement[xfields][description]',
							'Описание',
							true,
							$flixcdn->config['xfields']['write']['description'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsYear',
							'replacement[xfields][year]',
							'Год выпуска',
							true,
							$flixcdn->config['xfields']['write']['year'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsDuration',
							'replacement[xfields][duration]',
							'Продолжительность видео',
							true,
							$flixcdn->config['xfields']['write']['duration'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsGenres',
							'replacement[xfields][genres]',
							'Жанры',
							true,
							$flixcdn->config['xfields']['write']['genres'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsCountries',
							'replacement[xfields][countries]',
							'Страны',
							true,
							$flixcdn->config['xfields']['write']['countries'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsAge',
							'replacement[xfields][age]',
							'Возрастное ограничение',
							true,
							$flixcdn->config['xfields']['write']['age'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementXfeildsPoster',
							'replacement[xfields][poster]',
							'Постер',
							true,
							$flixcdn->config['xfields']['write']['poster'] ? false : true
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

					<?php echo FlixCDNForm::group(
						'moduleReplacementXfieldsAdditional',
						'',
						FlixCDNForm::checkbox(
							'replacementSeoUrl',
							'replacement[seo][url]',
							'ЧПУ новости',
							true,
							$flixcdn->config['seo']['url'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementSeoTitle',
							'replacement[seo][title]',
							'Заголовок новости',
							true,
							$flixcdn->config['seo']['title'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementSeoMetaTitle',
							'replacement[seo][meta_title]',
							'Мета-заголовок новости',
							true,
							$flixcdn->config['seo']['meta']['title'] ? false : true
						) . FlixCDNForm::checkbox(
							'replacementSeoMetaDescription',
							'replacement[seo][meta_description]',
							'Мета-описание новости',
							true,
							$flixcdn->config['seo']['meta']['description'] ? false : true
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