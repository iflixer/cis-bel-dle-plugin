<?php

$replacement = new FlixCDNReplacement($flixcdn->config);

if ($flixcdn->config['on']) {
	$result = $replacement->thread();

	if ($result)
		die(json_encode($result));
	else
		die(json_encode(array(
			'status' => 'end',
			'code' => '#5',
		)));
} else
	die(json_encode(array(
		'status' => 'end',
		'code' => '#6',
	)));