<?php

require_once CDNHUB_DIR . '/classes/CDNHubApi.php';
require_once CDNHUB_DIR . '/classes/CDNHubNews.php';
require_once CDNHUB_DIR . '/classes/CDNHubUpdate.php';
require_once CDNHUB_DIR . '/classes/CDNHubView.php';

class CDNHub
{

	const VERSION = '3';

	public $config;

	public function __construct()
	{

		$this->config = require_once CDNHUB_DIR . '/config.php';

	}

	// Version

	public function version()
	{
        global $db;
		if (isset($db)) {
			$result = $db->super_query("SELECT version FROM dle_plugins WHERE name = 'CDNHub'");

			if ($result && isset($result['version']) && !empty($result['version'])) {
				return $result['version'];
			}
		}

		return self::VERSION;
	}

	// View

	public function view($areas)
	{

		$view = new CDNHubView($this->config);

		if ($areas && is_array($areas))
			foreach ($areas as $area)
				call_user_func(array($view, $area));

	}

	// Update

	public function update()
	{
		$update = new CDNHubUpdate($this->config);
		$update->start();
	}

}