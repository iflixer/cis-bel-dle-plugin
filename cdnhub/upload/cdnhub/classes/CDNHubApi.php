<?php

class CDNHubApi
{

	protected $config;

	public function __construct($apiConfig)
	{
		
		$this->config = $apiConfig;

		if (!$this->config['domain']) {
			$this->config['domain'] = 'https://futmax.info/';
		}

		if ($this->config['domain'])
			$this->config['domain'] .= 'api/';

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

	// Updates

	public function getUpdates()
	{
		
		$data = $this->get("{$this->config['domain']}updates?token={$this->config['token']}");

		if ($data['result'])
			return $data['result'];
		else
			return false;

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

		if ($data['error'])
			return false;
		else
			return $data['result'];

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

		if ($data['error'])
			return false;
		else
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

			$response = curl_exec($ch);

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