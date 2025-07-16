<?php

class CDNHubBase
{

	public $config;

	public function __construct($config)
	{

		$this->config = $config;

	}

	public function build()
	{

		global $config, $db, $PHP_SELF, $cdnhub, $action, $baseUrl;

		if (isset($_POST['base']) && $_POST['mass_action'] == 'add_news') {

			$items = array();

			if ($_POST['base']) foreach ($_POST['base'] as $value) {
				list($kinopoisk_id, $translator_id) = explode('-', $value);

				if ($kinopoisk_id)
					$items[] = array(
						'kinopoisk_id' => $kinopoisk_id,
						'translator_id' => $translator_id
					);
			}

			if ($items)
				$this->mass_insert($items);

		}

		$search = isset($_GET['search']) ? rawurldecode($_GET['search']) : null;

		$offset = isset($_GET['offset']) ? intval($_GET['offset']) : null;

		if (!$offset)
			$offset = 0;

		$api = new CDNHubApi($this->config['api']);

		if ($search) {
			
			$data = $api->base('kinopoisk_id', $search, $offset);

			if (!$data['result'])
				$data = $api->base('imdb_id', $search, $offset);

			if (!$data['result'])
				$data = $api->base('title', $search, $offset);

		} else {

			$data = $api->base('', '', $offset);

		}

		$page = ($offset / 25) + 1;

		$prev = $data['prev'] ? "?mod=cdnhub&action=base&offset={$data['prev']['offset']}" . ($search ? "&search={$search}" : '') : null;
		$next = $data['next'] ? "?mod=cdnhub&action=base&offset={$data['next']['offset']}" . ($search ? "&search={$search}" : '') : null;

		if ($data['result'])
			$data = $data['result'];
		else
			$data = array();

		require dirname(__FILE__) . '/../actions/base.php';

	}

	public function mass_insert($items)
	{

		$api = new CDNHubApi($this->config['api']);
		$update = new CDNHubUpdate($this->config);

		foreach ($items as $item) {

			$data = $api->search('kinopoisk_id', $item['kinopoisk_id']);

			if (empty($data)) {
				$data = $api->search('imdb_id', $item['kinopoisk_id']);
			}

			if ($data[0]) {
				if ($data[0]['type'] == 'movie')
					$update->movie_insert($data[0]);
				if ($data[0]['type'] == 'serial')
					$update->serial_insert($data[0]);
			}

		}

		$_SESSION['mass_insert_success'] = true;

		header("Location: {$_SERVER['HTTP_REFERER']}");
		exit;

	}

}