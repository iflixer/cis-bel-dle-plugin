<?php

// Html

function flixcdn_encode($string, $flags = ENT_COMPAT, $charset = 'utf-8') {

	return htmlspecialchars($string, $flags, $charset);

}

function flixcdn_decode($string, $flags = ENT_COMPAT) {

	return htmlspecialchars_decode($string, $flags);

}

function flixcdn_css($data) {

	if (is_array($data)) {
		$css = '';

		foreach ($data as $style)
			$css .= '<link rel="stylesheet" href="' . flixcdn_encode($style) . '">';

		return $css;
	} elseif (is_string($data))
		return '<link rel="stylesheet" href="' . flixcdn_encode($data) . '">';
	else
		return false;

}

function flixcdn_js($data) {

	if (is_array($data)) {
		$js = '';

		foreach ($data as $script)
			$js .= '<script src="' . flixcdn_encode($script) . '"></script>';

		return $js;
	} elseif (is_string($data))
		return '<script src="' . flixcdn_encode($data) . '"></script>';
	else
		return false;

}