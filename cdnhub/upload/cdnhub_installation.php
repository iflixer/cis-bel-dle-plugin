<?php

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);

define('ROOT_DIR', dirname(__FILE__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once ENGINE_DIR . '/data/config.php';

header("Content-Type:text/html; charset={$config['charset']}");

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';

// Intsallation

$db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_cdnhub_update` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_update` int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=" . COLLATE . ";");

$db->query("INSERT INTO `" . PREFIX . "_cdnhub_update` (`id`, `start_update`) VALUES (1, 0);");

$db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_cdnhub_update_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `update_id` int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=" . COLLATE . ";");

$db->query("INSERT INTO `" . PREFIX . "_cdnhub_update_log` (`id`, `update_id`) VALUES (1, 0), (2, 0);");

$db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_cdnhub_update_serials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `update_date` timestamp NOT NULL,
  `translation_id` int(11) NOT NULL,
  `quality` varchar(255) NOT NULL,
  `season` int(11) NOT NULL,
  `episode` int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY `post_id` (`post_id`),
  KEY `token` (`token`),
  KEY `season` (`season`)
) ENGINE=InnoDB DEFAULT CHARSET=" . COLLATE . ";");

$db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_cdnhub_update_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_mark` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=" . COLLATE . ";");

$db->query("INSERT INTO `" . PREFIX . "_cdnhub_update_vote` (`id`, `vote_mark`, `session_id`) VALUES (1, 0, '');");

require_once ENGINE_DIR . '/api/api.class.php';

$dle_api->install_admin_module('cdnhub', 'CDNHub', '', 'engine/skins/images/cdnhub.png', '1');

echo 'Админпанель модуля CDNHub успешно установлена!';