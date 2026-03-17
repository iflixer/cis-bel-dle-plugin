<?php

$replacement = new FlixCDNReplacement($flixcdn->config);

if ($flixcdn->config['on']) {
	$result = $replacement->threads();
	
	if ($result)
		die(json_encode($result));
	else
		die(json_encode(array(
			'status' => 'end',
			'code' => '#2',
		)));
} else
	die(json_encode(array(
		'status' => 'end',
		'code' => '#1',
	)));