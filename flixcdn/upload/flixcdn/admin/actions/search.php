<?php

$flixcdnApi = new FlixCDNApi($flixcdn->config['api']);

$search_key = isset($_GET['field']) ? $_GET['field'] : null;
$search_value = isset($_GET['field']) ? rawurldecode($_GET['value']) : null;

if (!$search_key || !$search_value) {
	echo json_encode(array('notfound' => true));
	exit;
}

$flixcdnSearch = $flixcdnApi->search($search_key, $search_value);

if (!$flixcdnSearch) {
	echo json_encode(array('notfound' => true));
	exit;
}

$flixcdnUpdate = new FlixCDNUpdate($flixcdn->config);

$flixcdnResult = array();

foreach ($flixcdnSearch as $data) {

	$search_data = array();

	if ($flixcdn->config['xfields']['search']['kinopoisk_id'] && $data['kinopoisk_id'])
		$search_data['kinopoisk_id'] = $data['kinopoisk_id'];
	else
		$search_data['kinopoisk_id'] = '';

	if ($flixcdn->config['xfields']['search']['imdb_id'] && $data['imdb_id'])
		$search_data['imdb_id'] = $data['imdb_id'];
	else
		$search_data['imdb_id'] = '';



	if ($flixcdn->config['xfields']['write']['source'] && $data['iframe_url'])
		$search_data['source'] = $data['iframe_url'];
	else
		$search_data['source'] = '';



	if ($data['quality'])
		$search_data['quality'] = $data['quality'];
	else
		$search_data['quality'] = '';

	if ($flixcdn->config['xfields']['write']['custom_quality'] && $data['quality'])
		$search_data['custom_quality'] = $flixcdnUpdate->custom_replacement($data['quality'], $flixcdn->config['custom']['qualities']);
	else
		$search_data['custom_quality'] = '';



	if ($data['translations'][0])
		$search_data['translation'] = $data['translations'][0]['title'];
	else
		$search_data['translation'] = '';

	if ($flixcdn->config['xfields']['write']['translation'] && $data['translations'][0])
		$search_data['custom_translation'] = $flixcdnUpdate->custom_replacement($data['translations'][0]['title'], $flixcdn->config['custom']['translations']);
	else
		$search_data['custom_translation'] = '';

	if (($flixcdn->config['xfields']['write']['translations'] || $flixcdn->config['xfields']['write']['custom_translations']) && $data['translations']) {
		$_translations = [];
		$_custom_translations = [];
		foreach ($data['translations'] as $_translation) {
			$_translations[] = $_translation['title'];
			$_custom_translations[] = $flixcdnUpdate->custom_replacement($_translation['title'], $flixcdn->config['custom']['translations']);;
		}
		$search_data['translations'] = implode(', ', $_translations);
		$search_data['custom_translations'] = implode(', ', $_custom_translations);
	} else {
		$search_data['translations'] = '';
		$search_data['custom_translations'] = '';
	}



	if ($data['title_rus'])
		$search_data['title_rus'] = $data['title_rus'];
	else
		$search_data['title_rus'] = '';

	if ($data['title_orig'])
		$search_data['title_orig'] = $data['title_orig'];
	else
		$search_data['title_orig'] = '';

	if ($flixcdn->config['xfields']['write']['slogan'] && $data['slogan'])
		$search_data['slogan'] = $data['slogan'];
	else
		$search_data['slogan'] = '';

	if ($flixcdn->config['xfields']['write']['description'] && $data['description'])
		$search_data['description'] = $data['description'];
	else
		$search_data['description'] = '';

	if ($flixcdn->config['xfields']['write']['year'] && $data['year'])
		$search_data['year'] = $data['year'];
	else
		$search_data['year'] = '';

	if ($flixcdn->config['xfields']['write']['duration'] && $data['duration'])
		$search_data['duration'] = $data['duration'];
	else
		$search_data['duration'] = '';

	if (($flixcdn->config['genres_storage'] === 'categories' || $flixcdn->config['xfields']['write']['genres']) && $data['genres'])
		$search_data['genres'] = implode(', ', $data['genres']);
	else
		$search_data['genres'] = '';

	if ($flixcdn->config['xfields']['write']['countries'] && $data['countries'])
		$search_data['countries'] = implode(', ', $data['countries']);
	else
		$search_data['countries'] = '';

	if ($flixcdn->config['xfields']['write']['age'] && $data['age'])
		$search_data['age'] = $data['age'];
	else
		$search_data['age'] = '';

	if ($flixcdn->config['xfields']['write']['poster'] && $data['poster'])
		$search_data['poster'] = $data['poster'];
	else
		$search_data['poster'] = '';



	if ($data['type'] == 'serial') {
		$search_data['season'] = '';
		$search_data['episode'] = '';

		$search_data['format_season'] = '';
		$search_data['format_episode'] = '';

		$update_season = $data['season'];
		$update_episode = $data['episode'];

		if ($update_season) {
			$search_data['season'] = $update_season;

			if ($flixcdn->config['xfields']['write']['format_season'] && $flixcdn->config['xfields']['write']['format_season_type'])
				$search_data['format_season'] = $flixcdnUpdate->format_season($flixcdn->config['xfields']['write']['format_season_type'], $update_season);
		}

		if (isset($update_episode) && $update_episode !== '') {
			$search_data['episode'] = $update_episode;

			if ($flixcdn->config['xfields']['write']['format_episode'] && $flixcdn->config['xfields']['write']['format_episode_type'])
				$search_data['format_episode'] = $flixcdnUpdate->format_episode($flixcdn->config['xfields']['write']['format_episode_type'], $update_episode);
		}

		$search_data['type'] = 'serial';
		$search_data['type_ru'] = 'Серіал';
	} else {
		$search_data['season'] = '';
		$search_data['episode'] = '';

		$search_data['format_season'] = '';
		$search_data['format_episode'] = '';

		$search_data['type'] = 'movie';
		$search_data['type_ru'] = 'Фільм';
	}

	// Seo

	$search_data['seo_url'] = '';
	$search_data['seo_title'] = '';
	$search_data['seo_meta_title'] = '';
	$search_data['seo_meta_description'] = '';

	if ($flixcdn->config['seo']['on']) {

		if ($flixcdn->config['seo']['url']) {
			$seo_url = $flixcdnUpdate->seo($search_data, $flixcdn->config['seo']['url'], true);

			if ($seo_url)
				$search_data['seo_url'] = $seo_url;
		}

		if ($flixcdn->config['seo']['title']) {
			$seo_title = $flixcdnUpdate->seo($search_data, $flixcdn->config['seo']['title']);

			if ($seo_title)
				$search_data['seo_title'] = $seo_title;
		}

		if ($flixcdn->config['seo']['meta']['title']) {
			$seo_meta_title = $flixcdnUpdate->seo($search_data, $flixcdn->config['seo']['meta']['title']);

			if ($seo_meta_title)
				$search_data['seo_meta_title'] = $seo_meta_title;
		}

		if ($flixcdn->config['seo']['meta']['description']) {
			$seo_meta_description = $flixcdnUpdate->seo($search_data, $flixcdn->config['seo']['meta']['description']);

			if ($seo_meta_description)
				$search_data['seo_meta_description'] = $seo_meta_description;
		}

	}

	$flixcdnResult[] = $search_data;

}

echo json_encode(array(
	'success' => true,
	'result' => $flixcdnResult
));