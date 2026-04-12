<?php

class FlixCDNApi
{

	protected $config;
	protected $version;
	protected $latestVersion;

	protected static $fieldMap = [
		'ru_name'           => 'title_rus',
		'name'              => 'title_orig',
		'image_url'         => 'poster',
		'best_quality'      => 'quality',
		'film_length'       => 'duration',
		'rating_age_limits' => 'age',
	];

	public function __construct($apiConfig)
	{

		$this->config = $apiConfig;
		$this->version = $apiConfig['version'] ?? null;

		if ($this->config['domain'])
			$this->config['domain'] .= 'api/';

	}

	public function getLatestVersion()
	{
		return $this->latestVersion;
	}

	private static $seriesTypes = [
		'serial', 'tv-series', 'anime-tv-series', 'tv-show', 'cartoon-series',
		'episode', 'tvshow', 'animeserial', 'showserial',
	];

	protected static function normalizeItem($item)
	{
		if (!$item || !is_array($item))
			return $item;

		foreach (self::$fieldMap as $new => $old) {
			if (array_key_exists($new, $item) && !array_key_exists($old, $item)) {
				$item[$old] = $item[$new];
			}
		}

		if (!array_key_exists('episode', $item))
			$item['episode'] = '';

		if (!array_key_exists('season', $item))
			$item['season'] = '';

		$item['original_type'] = $item['type'] ?? '';

		if (array_key_exists('has_series', $item)) {
			$item['type'] = $item['has_series'] ? 'serial' : 'movie';
		} elseif (isset($item['type'])) {
			$item['type'] = in_array($item['type'], self::$seriesTypes, true) ? 'serial' : 'movie';
		}

		return $item;
	}

	// Translations

	public function getTranslations()
	{

		$data = $this->get("{$this->config['domain']}translations?token={$this->config['token']}");
		if ($data['result'])
			return $data['result'];
		else
			return false;

	}

	public function getGenres()
	{
		$data = $this->get("{$this->config['domain']}genres?token={$this->config['token']}");

		if ($data['result'])
			return $data['result'];
		else
			return [];
	}

	// Updates

	public function getUpdates()
	{

		$data = $this->get("{$this->config['domain']}updates?token={$this->config['token']}");

		if (!$data['result'])
			return false;

		foreach (['movies', 'serials'] as $type) {
			if (!isset($data['result'][$type]))
				continue;

			foreach ($data['result'][$type] as &$entry) {
				if (isset($entry['content']))
					$entry['content'] = self::normalizeItem($entry['content']);
			}
			unset($entry);
		}

		return $data['result'];

	}

	// Search

	public function search($key, $value)
	{

		if ($key == 'kinopoisk_id')
			$key = 'kinopoisk_id';

		if ($key == 'imdb_id')
			$key = 'imdb_id';

		if ($key == 'title')
			$key = 'title';

		$data = $this->get("{$this->config['domain']}search?token={$this->config['token']}&" . rawurlencode($key) . '=' . rawurlencode($value));

		if (!empty($data['error']))
			return false;

		return array_map([self::class, 'normalizeItem'], $data['result'] ?: []);

	}

	// Base

	public function base($field = '', $value = '', $offset = 0, $limit = 25)
	{

		$url = "{$this->config['domain']}search?token={$this->config['token']}";

		if ($field && $value)
			$url .= "&{$field}={$value}";

		if ($offset)
			$url .= "&offset={$offset}";

		if ($limit)
			$url .= "&limit={$limit}";

		$data = $this->get($url);

		if (!empty($data['error']))
			return false;

		if (isset($data['result']))
			$data['result'] = array_map([self::class, 'normalizeItem'], $data['result']);

		return $data;

	}

	// Get

	private function get($url)
	{

		if ($ch = curl_init($url)) {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);

			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			if ($this->version) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Plugin-Version: ' . $this->version]);
			}

			$latestVersion = null;
			curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$latestVersion) {
				if (stripos($header, 'X-Latest-Plugin-Version:') === 0) {
					$latestVersion = trim(substr($header, strlen('X-Latest-Plugin-Version:')));
				}
				return strlen($header);
			});

			$response = curl_exec($ch);

			if ($latestVersion && preg_match('/^\d+(\.\d+)*$/', $latestVersion)) {
				$this->latestVersion = $latestVersion;
				if (defined('FLIXCDN_DIR')) {
					@file_put_contents(FLIXCDN_DIR . '/.latest_version', $latestVersion);
				}
			}

			if ($response === false)
				$data = array('error' => 'cURL error: ' . curl_error($ch));
			else
				$data = json_decode($response, true);

			curl_close($ch);
		} else
			$data = array('error' => 'cURL is not installed in your PHP installation');

		return $data;

	}

}