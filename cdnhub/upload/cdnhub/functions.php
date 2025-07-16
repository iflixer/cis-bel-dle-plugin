<?php

// Html

function cdnhub_encode($string, $flags = ENT_COMPAT, $charset = 'utf-8') {

	return htmlspecialchars($string, $flags, $charset);

}

function cdnhub_decode($string, $flags = ENT_COMPAT) {

	return htmlspecialchars_decode($string, $flags);

}

function cdnhub_css($data) {

	if (is_array($data)) {
		$css = '';

		foreach ($data as $style)
			$css .= '<link rel="stylesheet" href="' . cdnhub_encode($style) . '">';

		return $css;
	} elseif (is_string($data))
		return '<link rel="stylesheet" href="' . cdnhub_encode($data) . '">';
	else
		return false;

}

function cdnhub_js($data) {

	if (is_array($data)) {
		$js = '';

		foreach ($data as $script)
			$js .= '<script src="' . cdnhub_encode($script) . '"></script>';

		return $js;
	} elseif (is_string($data))
		return '<script src="' . cdnhub_encode($data) . '"></script>';
	else
		return false;

}