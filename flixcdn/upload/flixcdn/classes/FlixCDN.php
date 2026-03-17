<?php

require_once FLIXCDN_DIR . '/classes/FlixCDNApi.php';
require_once FLIXCDN_DIR . '/classes/FlixCDNNews.php';
require_once FLIXCDN_DIR . '/classes/FlixCDNUpdate.php';
require_once FLIXCDN_DIR . '/classes/FlixCDNView.php';

class FlixCDN
{

	const VERSION = '3';

	public $config;

	public function __construct()
	{

		$this->config = require_once FLIXCDN_DIR . '/config.php';

	}

	// Version

	public function version()
	{
        global $db;
		if (isset($db)) {
			$result = $db->super_query("SELECT version FROM dle_plugins WHERE name = 'FlixCDN'");

			if ($result && isset($result['version']) && !empty($result['version'])) {
				return $result['version'];
			}
		}

		return self::VERSION;
	}

	// View

	public function view($areas)
	{

		$view = new FlixCDNView($this->config);

		if ($areas && is_array($areas))
			foreach ($areas as $area)
				call_user_func(array($view, $area));

	}

	// Update

	public function update()
	{
		$update = new FlixCDNUpdate($this->config);
		$update->start();
	}

}