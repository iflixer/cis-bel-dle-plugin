<?php

@set_time_limit(0);

@error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('AUTOMODE', true);
define('LOGGED_IN', true);

define('ROOT_DIR', dirname(__FILE__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once ENGINE_DIR . '/data/config.php';

date_default_timezone_set($config['date_adjust']);

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';

if (file_exists(ENGINE_DIR . '/classes/plugins.class.php') && !class_exists('DLEPlugins'))
	require_once ENGINE_DIR . '/classes/plugins.class.php';

require_once ENGINE_DIR . '/modules/functions.php';

// Update

require_once ROOT_DIR . '/cdnhub/init.php';

if (!$cdnhub->config['cronkey'] || $_GET['key'] != $cdnhub->config['cronkey'])
	exit;

if (intval($cdnhub->config['update']['type']))
	$cdnhub->update();