<?php

// Module

require_once dirname(__FILE__) . '/../init.php';

// Admin

define('FLIXCDN_ADMIN_DIR', dirname(__FILE__));

$baseUrl = $PHP_SELF . '?mod=flixcdn';

require_once FLIXCDN_ADMIN_DIR . '/functions.php';

require_once FLIXCDN_ADMIN_DIR . '/classes/FlixCDNForm.php';
require_once FLIXCDN_ADMIN_DIR . '/classes/FlixCDNReplacement.php';
require_once FLIXCDN_ADMIN_DIR . '/classes/FlixCDNBase.php';

// Route

$actionDir = FLIXCDN_ADMIN_DIR . '/actions';

switch ($action) {

	// Base

	case 'base':

		$flixcdnBase = new FlixCDNBase($flixcdn->config);
		$flixcdnBase->build();

		break;

	// Settings

	case 'settings':

		require_once $actionDir . '/settings.php';

		break;

	// Replacement

	case 'replacement':

		require_once $actionDir . '/replacement.php';

		break;

	// Replacement Threads

	case 'replacement_threads':

		require_once $actionDir . '/replacement_threads.php';

		break;

	// Replacement Thread

	case 'replacement_thread':

		require_once $actionDir . '/replacement_thread.php';

		break;

	// Search

	case 'search':

		require_once $actionDir . '/search.php';

		break;

	case 'load_mapping_data':

		require_once $actionDir . '/load_mapping_data.php';

		break;

	// Index

	default:
		
		if ($_SERVER['REQUEST_URI'] != $baseUrl)
			header("Location: {$baseUrl}");

		require_once $actionDir . '/index.php';

		break;

}