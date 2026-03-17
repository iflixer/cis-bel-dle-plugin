<?php

class FlixCDNView
{

	protected $config;

	protected $xfields;

	public function __construct($moduleConfig)
	{

		$this->config = $moduleConfig;

		$this->xfields = new FlixCDNNewsXfields;

	}

	// Script

	public function script()
	{

		global $config, $tpl;

		if (defined('NEWS_ID')) {
			$tpl->set('</body>', flixcdn_js('/flixcdn/assets/js/actualize.js') . '</body>');

			return true;
		} else
			return false;

	}

	// Player

	public function player()
	{

		global $config, $tpl, $row;

		if (!$this->config['on'] || !$row || !$this->config['xfields']['write']['source'] || stripos($tpl->copy_template, '{flixcdn-player}') === false) {
			$tpl->set_block("'\\[flixcdn-notfound\\](.*?)\\[/flixcdn-notfound\\]'is", "$1");
			$tpl->set_block("'\\[flixcdn-found\\](.*?)\\[/flixcdn-found\\]'is", '');
			$tpl->set('{flixcdn-player}', '');

			return false;
		}

		$xfields = $this->xfields->toArray($row['xfields']);

		if (!$xfields || !$xfields[$this->config['xfields']['write']['source']]) {
			$tpl->set_block("'\\[flixcdn-notfound\\](.*?)\\[/flixcdn-notfound\\]'is", "$1");
			$tpl->set_block("'\\[flixcdn-found\\](.*?)\\[/flixcdn-found\\]'is", '');
			$tpl->set('{flixcdn-player}', '');

			return false;
		}

		$source = $xfields[$this->config['xfields']['write']['source']];

		$iframe = "<div style=\"position:relative;padding-bottom:53.10%;padding-top:25px;height:0\">
			<iframe src=\"{$source}\" id=\"flixcdn\" style=\"position:absolute;top:0;left:0;width:100%;height:100%\" frameborder=\"0\" allowfullscreen></iframe>
		</div>";

		$tpl->set_block("'\\[flixcdn-notfound\\](.*?)\\[/flixcdn-notfound\\]'is", '');
		$tpl->set_block("'\\[flixcdn-found\\](.*?)\\[/flixcdn-found\\]'is", "$1");
		$tpl->set('{flixcdn-player}', $iframe);

		return true;

	}

}