<?php

require_once dirname(__FILE__) . '/CDNHubNewsXfields.php';

class CDNHubNews
{

	protected $id;

	protected $user;

	public $config;

	public $data = array();
	public $xfields;

	public $approve = false;

	public function __construct($post_id = null)
	{

		$this->xfields = new CDNHubNewsXfields;

		$this->id = $post_id;

	}

	// Save

	public function save()
	{

		global $db;

		if (!$this->data)
			return false;

		$data = $this->data;

		// foreach ($this->config['xfields']['search'] as $xfield)
		// 	unset($data['xfields'][$xfield]);

		foreach ($data as $key => $item) {
			if (!$item)
				unset($data[$key]);
		}

		if (!$data)
			return false;

		if ($this->id) {

			if ($this->data['xfields'])
				$this->data['xfields'] = $this->xfields->toString($this->data['xfields']);

			$sets = array();

			foreach ($this->data as $key => $value) {
				$value = $db->safesql($value);
				$sets[] = "`{$key}` = '{$value}'";
			}

			$query = "UPDATE " . PREFIX . "_post SET " . implode(', ', $sets) . " WHERE `id` = '{$this->id}'";

			$db->query($query);

			if ($this->approve && $xfields)
				$this->xfields->xfsearch($this->id, $xfields);

			clear_cache(array('news_', 'full_' . $this->id, 'comm_' . $this->id, 'tagscloud_', 'archives_', 'related_', 'calendar_', 'rss', 'stats'));

		} else {

			if (!$this->user)
				$this->user = $db->super_query("SELECT user_id `id`, `name` FROM " . USERPREFIX . "_users WHERE `user_group` = 1 ORDER BY `user_id` ASC LIMIT 1");

			$this->data['autor'] = $this->user['name'];

			$this->data['short_story'] = '';
			$this->data['full_story'] = '';

			$this->data['category'] = '';
			$this->data['date'] = date('Y-m-d H:i:s', time());
			$this->data['approve'] = 0;

			$this->data['xfields'] = $this->xfields->toString($this->data['xfields']);

			$sets = array();

			foreach ($this->data as $key => $value) {
				$value = $db->safesql($value);
				$sets[] = "`{$key}` = '{$value}'";
			}

			$sets[] = "`keywords` = ''";

			$db->query("INSERT INTO " . PREFIX . "_post SET " . implode(', ', $sets));

			$post_id = $db->insert_id();

			$db->query("INSERT INTO " . PREFIX . "_post_extras SET `news_id` = '{$post_id}', `editor` = '{$this->user['name']}', `user_id` = '{$this->user['id']}'");

			return $post_id;

		}

		return true;

	}

}