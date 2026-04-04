<?php

define('FLIXCDN_DIR', dirname(__FILE__));

require_once FLIXCDN_DIR . '/functions.php';

require_once FLIXCDN_DIR . '/classes/FlixCDN.php';
$flixcdn = new FlixCDN;
$flixcdn->config['api']['version'] = $flixcdn->version();