<?php

class CDNHubView
{

	protected $config;

	protected $xfields;

	public function __construct($moduleConfig)
	{

		$this->config = $moduleConfig;

		$this->xfields = new CDNHubNewsXfields;

	}

	// Script

	public function script()
	{

		global $config, $tpl;

		if (defined('NEWS_ID')) {
			$tpl->set('</body>', cdnhub_js('/cdnhub/assets/js/actualize.js') . '</body>');

			return true;
		} else
			return false;

	}

	// Player

	public function player()
	{

		global $config, $tpl, $row;

		if (!$this->config['on'] || !$row || !$this->config['xfields']['write']['source'] || stripos($tpl->copy_template, '{cdnhub-player}') === false) {
			$tpl->set_block("'\\[cdnhub-notfound\\](.*?)\\[/cdnhub-notfound\\]'is", "$1");
			$tpl->set_block("'\\[cdnhub-found\\](.*?)\\[/cdnhub-found\\]'is", '');
			$tpl->set('{cdnhub-player}', '');

			return false;
		}

		$xfields = $this->xfields->toArray($row['xfields']);

		if (!$xfields || !$xfields[$this->config['xfields']['write']['source']]) {
			$tpl->set_block("'\\[cdnhub-notfound\\](.*?)\\[/cdnhub-notfound\\]'is", "$1");
			$tpl->set_block("'\\[cdnhub-found\\](.*?)\\[/cdnhub-found\\]'is", '');
			$tpl->set('{cdnhub-player}', '');

			return false;
		}

		$source = $xfields[$this->config['xfields']['write']['source']];

		$iframe = "<div style=\"position:relative;padding-bottom:53.10%;padding-top:25px;height:0\">
			<iframe src=\"{$source}\" id=\"cdnhub\" style=\"position:absolute;top:0;left:0;width:100%;height:100%\" frameborder=\"0\" allowfullscreen></iframe>
		</div>";

		$tpl->set_block("'\\[cdnhub-notfound\\](.*?)\\[/cdnhub-notfound\\]'is", '');
		$tpl->set_block("'\\[cdnhub-found\\](.*?)\\[/cdnhub-found\\]'is", "$1");
		$tpl->set('{cdnhub-player}', $iframe);

		return true;

	}

}