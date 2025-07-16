<?php

class CDNHubUpdate
{

	protected $config;

	protected $first = false;

	public $search = array(
		'kinopoisk_id',
		'imdb_id',
	);

	public $quality = array(
		'camrip',
		'ts',
		'dvdscr',
		'tc',
		'dvdrip',
		'webrip',
		'hdtvrip',
		'hdtv',
		'web-dlrip',
		'webdlrip',
		'hdrip',
		'hddvd',
		'web-dl',
		'webdl',
		'bdrip',
		'bdremux',
		'bd',
		'bluray',
		'4k'
	);

	public $added = array();

	public function __construct($config)
	{

		$this->config = $config;

	}

	// Start

	public function start()
	{

		global $db;

		if (!$this->config['on'])
			return false;

		if (!$this->config['update']['movies']['on'] && !$this->config['update']['serials']['on'])
			return false;

		$search = false;

		foreach ($this->search as $field) {
			if ($this->config['xfields']['search'][$field])
				$search = true;
		}

		if (!$search)
			return false;

		if (!$this->config['xfields']['write']['source'])
			return false;

		$cron = intval($this->config['update']['type']) === 1 ? true : false;

		if ($cron) {
			$row = $db->super_query("SELECT `start_update` FROM ". PREFIX . "_cdnhub_update ORDER BY `start_update` DESC LIMIT 1");
			$last_update = intval($row['start_update']) ? intval($row['start_update']) : false;

			if (!$last_update)
				$this->first = true;

			$this->update();
		} else {

			$row = $db->super_query("SELECT `start_update` FROM ". PREFIX . "_cdnhub_update ORDER BY `start_update` DESC LIMIT 1");
			$last_update = intval($row['start_update']) ? intval($row['start_update']) : false;

			if ($last_update) {

				$interval = $this->config['update']['interval'];

				if (stripos($interval, 'h') !== false)
					$interval = (intval($interval) * 60 * 60);
				elseif (stripos($interval, 'm') !== false)
					$interval = (intval($interval) * 60);
				else
					$interval = (60 * 60 * 3); // default inerval 3 hours

				// $next_update = $last_update + $interval;
				$next_update = $last_update + 1; // test

				if ($next_update > time())
					return false;

			} else
				$this->first = true;

			$vote_mark = time();
			$session_id = session_id();

			$db->query("UPDATE " . PREFIX . "_cdnhub_update_vote SET `vote_mark` = {$vote_mark}, `session_id` = '{$session_id}' WHERE `vote_mark` < " . ($vote_mark - 60));

			$row = $db->super_query("SELECT `session_id` FROM " . PREFIX . "_cdnhub_update_vote WHERE `vote_mark` > 0 LIMIT 1");

			if ($session_id != $row['session_id'])
				return false;

			$this->update();

		}

	}

	// Update

	protected function update()
	{

		global $db;

		$db->query("UPDATE " . PREFIX . "_cdnhub_update SET `start_update` = " . time());

		$api = new CDNHubApi($this->config['api']);

		$updates = $api->getUpdates();

		if ($updates['movies'])
			$this->movies($updates['movies']);

		if ($updates['serials'])
			$this->serials($updates['serials']);

	}

	// Search

	protected function search($updates)
	{

		global $db;

		$find = array();
		$select = array();

		foreach ($this->search as $search) {
			if (!$this->config['xfields']['search'][$search])
				continue;

			$xfield = $this->config['xfields']['search'][$search];

			$ids = array();

			foreach ($updates as $update) {
				if ($update['content'][$search])
					$ids[] = $update['content'][$search];
			}

			if ($ids) {
				$select[] = "SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$xfield}|', -1), '||', 1) `{$search}`";
				$find[] = "SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$xfield}|', -1), '||', 1) IN ('" . implode("','", $ids) . "')";
			}
		}

		$query = 'SELECT *, ' . implode(', ', $select) . ' FROM ' . PREFIX . '_post';
		
		if ($find)
			$query .= ' WHERE ' . implode(' OR ', $find);

		$result = $db->query($query);

		while ($row = $db->get_row($result)) {
			foreach ($this->search as $search) {
				if (!$this->config['xfields']['search'][$search])
					continue;

				if ($row[$search]) {
					foreach ($updates as $key => $update) {
						if ($update['content'][$search] == $row[$search])
							$updates[$key]['post'] = $row;
					}
				}
			}
		}

		$db->free();

		return $updates;

	}

	// Movies

	protected function movies($data)
	{

		global $db;

		if (!$data)
			return false;

		if ($this->first) {
			$end = end($data);
			$db->query("UPDATE " . PREFIX . "_cdnhub_update_log SET `update_id` = " . (intval($end['update_id']) ? intval($end['update_id']) : 0) . " WHERE `id` = 1");
			return false;
		}

		$row = $db->super_query("SELECT `update_id` FROM " . PREFIX . "_cdnhub_update_log WHERE `id` = 1");
		$last_update_id = $row['update_id'] ? $row['update_id'] : 0;

		krsort($data);

		$need_update = array();

		foreach ($data as $entry) {
			if ($entry['update_id'] > $last_update_id)
				$need_update[] = $entry;
		}

		if (!$need_update)
			return false;

		$updates = $this->search($need_update);

		foreach ($updates as $update) {
			if ($this->config['update']['movies']['on'] && ($update['post'] || $this->added[$update['content']['id']])) {
				if ($this->added[$update['content']['id']])
					$update['post'] = $this->added[$update['content']['id']];

				$this->movie_update($update);
			} else {
				if ($this->config['update']['movies']['add'])
					$this->movie_insert($update);
			}

			$db->query("UPDATE " . PREFIX . "_cdnhub_update_log SET `update_id` = " . intval($update['update_id']) . " WHERE `id` = 1");
		}

		return true;

	}

	protected function movie_update($data)
	{

		$news = new CDNHubNews($data['post']['id']);

		$news->config = $this->config;

		if (intval($data['post']['approve']))
			$news->approve = true;

		$xfields = $news->xfields->toArray($data['post']['xfields']);

		if ($this->config['xfields']['not_update'] && intval($xfields[$this->config['xfields']['not_update']]))
			return false;

		// Build

		$fields = array(
			
			'content|kinopoisk_id',
            'content|imdb_id',

            'content|iframe_url',

            'content|title_rus',
            'content|title_orig',
            'content|slogan',
            'content|description',
            'content|year',
            'content|duration',
            'content|genres',
            'content|countries',
            'content|age',
            'content|poster',
            
            'content|quality',
            'quality_custom',

            'translation',
            'translation_custom',
            'translations',
            'translations_custom',

		);

		$update_data = array();

		foreach ($fields as $field) {
			if ($data[$field]) {
                $update_data[$field] = $data[$field];
            } elseif (strpos($field, '|') !== false) {
                list($lvl1, $lvl2) = explode('|', $field);

                if ($data[$lvl1][$lvl2])
                    $update_data[$lvl2] = $data[$lvl1][$lvl2];
            }
		}

		// Xfields

		$news->data['xfields'] = $xfields;

		if ($data['translation'])
            $update_data['translation'] = $data['translation']['title'];
        else
            $update_data['translation'] = '';

        if ($this->config['xfields']['write']['custom_quality'] && $update_data['quality'])
            $update_data['custom_quality'] = $this->custom_replacement($update_data['quality'], $this->config['custom']['qualities']);
        else
            $update_data['custom_quality'] = '';


        if ($this->config['xfields']['write']['translation'] && $update_data['translation'])
            $update_data['custom_translation'] = $this->custom_replacement($update_data['translation'], $this->config['custom']['translations']);
        else
            $update_data['custom_translation'] = '';

        if (($this->config['xfields']['write']['translations'] || $this->config['xfields']['write']['custom_translations']) && $data['content']['translations']) {
            $_translations = [];
            $_custom_translations = [];
            foreach ($data['content']['translations'] as $_translation) {
                $_translations[] = $_translation['title'];
                $_custom_translations[] = $this->custom_replacement($_translation['title'], $this->config['custom']['translations']);
            }
            $update_data['translations'] = implode(', ', $_translations);
            $update_data['custom_translations'] = implode(', ', $_custom_translations);
        } else {
            $update_data['translations'] = '';
            $update_data['custom_translations'] = '';
        }

		if ($update_data['iframe_url'] && $xfields[$this->config['xfields']['write']['source']])
			$update_data['iframe_url'] = $this->save_source_params($update_data['iframe_url'], $xfields[$this->config['xfields']['write']['source']]);

		// News

		if ($this->config['xfields']['write']['source'] && $update_data['iframe_url'] && $update_data['iframe_url'] != $xfields[$this->config['xfields']['write']['source']])
			$news->data['xfields'][$this->config['xfields']['write']['source']] = $update_data['iframe_url'];

		if ($this->config['xfields']['write']['quality'] && $update_data['quality'] && $update_data['quality'] != $xfields[$this->config['xfields']['write']['quality']]) {
			$news->data['xfields'][$this->config['xfields']['write']['quality']] = $update_data['quality'];

		if ($this->config['xfields']['write']['custom_quality'] && $update_data['quality']) {
			$update_data['custom_quality'] = $this->custom_replacement($update_data['quality'], $this->config['custom']['qualities']);

			if ($update_data['custom_quality'] != $xfields[$this->config['xfields']['write']['custom_quality']])
				$news->data['xfields'][$this->config['xfields']['write']['custom_quality']] = $update_data['custom_quality'];
		}

		if ($this->config['xfields']['write']['custom_translation'] && $update_data['translation']) {
			$update_data['custom_translation'] = $this->custom_replacement($update_data['translation'], $this->config['custom']['translations']);

			if ($update_data['custom_translation'] != $xfields[$this->config['xfields']['write']['custom_translation']])
				$news->data['xfields'][$this->config['xfields']['write']['custom_translation']] = $update_data['custom_translation'];
		}

		if ($this->config['xfields']['write']['translation'] && $update_data['translation'] && $update_data['translation'] != $xfields[$this->config['xfields']['write']['translation']])
			$news->data['xfields'][$this->config['xfields']['write']['translation']] = $update_data['translation'];

			if ($this->config['update']['movies']['up'] && intval($data['post']['approve']) && strtotime($data['post']['date']) <= time()) {
				$post_quality_key = intval(array_search($xfields[$this->config['xfields']['write']['quality']], $this->quality));
				$update_quality_key = intval(array_search($update_data['quality'], $this->quality));

				if ($xfields[$this->config['xfields']['write']['quality']] && $update_quality_key > $post_quality_key)
					$news->data['date'] = date('Y-m-d H:i:s', time());
			}
		}

		if ($news->data['xfields'] == $xfields)
			unset($news->data['xfields']);

		// Seo

		$update_data['type'] = 'movie';

		if ($this->config['seo']['on']) {
			if ($this->config['seo']['url']) {
				$seo_url = $this->seo($update_data, $this->config['seo']['url'], true);

				if ($seo_url != $data['post']['alt_name'])
					$news->data['alt_name'] = $seo_url;
			}

			if ($this->config['seo']['title']) {
				$seo_title = $this->seo($update_data, $this->config['seo']['title']);

				if ($seo_title != $data['post']['title'])
					$news->data['title'] = $seo_title;
			}

			if ($this->config['seo']['meta']['title']) {
				$seo_meta_title = $this->seo($update_data, $this->config['seo']['meta']['title']);

				if ($seo_meta_title != $data['post']['metatitle'])
					$news->data['metatitle'] = $seo_meta_title;
			}

			if ($this->config['seo']['meta']['description']) {
				$seo_meta_description = $this->seo($update_data, $this->config['seo']['meta']['description']);

				if ($seo_meta_description != $data['post']['descr'])
					$news->data['descr'] = $seo_meta_description;
			}
		}

		$news->save();

		$this->added[$data['content']['id']] = array_merge(['id' => $data['post']['id']], $news->data);

	}

	public function movie_insert($data)
	{

		$news = new CDNHubNews;

		$news->config = $this->config;

		// Build

		if (!isset($data['content']))
			$data = ['content' => $data];

		$fields = array(
			
			'content|kinopoisk_id',
			'content|imdb_id',

			'content|iframe_url',

			'content|title_rus',
			'content|title_orig',
			'content|slogan',
			'content|description',
			'content|year',
			'content|duration',
			'content|genres',
			'content|countries',
			'content|age',
			'content|poster',
			
			'content|quality',

		);

		$insert_data = array();

		foreach ($fields as $field) {
			if ($data[$field]) {
				$insert_data[$field] = $data[$field];
			} elseif (strpos($field, '|') !== false) {
				list($lvl1, $lvl2) = explode('|', $field);

				if ($data[$lvl1][$lvl2])
					$insert_data[$lvl2] = $data[$lvl1][$lvl2];
			}
		}

		// Xfields

		if ($data['content']['translations'][0]['title'])
			$insert_data['translation'] = $data['content']['translations'][0]['title'];
		else
			$insert_data['translation'] = '';

		if ($this->config['xfields']['write']['custom_quality'] && $insert_data['quality'])
			$insert_data['custom_quality'] = $this->custom_replacement($insert_data['quality'], $this->config['custom']['qualities']);
		else
			$insert_data['custom_quality'] = '';


		if ($this->config['xfields']['write']['translation'] && $insert_data['translation'])
			$insert_data['custom_translation'] = $this->custom_replacement($insert_data['translation'], $this->config['custom']['translations']);
		else
			$insert_data['custom_translation'] = '';

		if (($this->config['xfields']['write']['translations'] || $this->config['xfields']['write']['custom_translations']) && $data['content']['translations']) {
			$_translations = [];
			$_custom_translations = [];
			foreach ($data['content']['translations'] as $_translation) {
				$_translations[] = $_translation['title'];
				$_custom_translations[] = $this->custom_replacement($_translation['title'], $this->config['custom']['translations']);
			}
			$insert_data['translations'] = implode(', ', $_translations);
			$insert_data['custom_translations'] = implode(', ', $_custom_translations);
		} else {
			$insert_data['translations'] = '';
			$insert_data['custom_translations'] = '';
		}

		// News

		if ($this->config['xfields']['search']['kinopoisk_id'] && $insert_data['kinopoisk_id'])
			$news->data['xfields'][$this->config['xfields']['search']['kinopoisk_id']] = $insert_data['kinopoisk_id'];

		if ($this->config['xfields']['search']['imdb_id'] && $insert_data['imdb_id'])
			$news->data['xfields'][$this->config['xfields']['search']['imdb_id']] = $insert_data['imdb_id'];

		if ($this->config['xfields']['write']['source'] && $insert_data['iframe_url'])
			$news->data['xfields'][$this->config['xfields']['write']['source']] = $insert_data['iframe_url'];

		if ($this->config['xfields']['write']['quality'] && $insert_data['quality'])
			$news->data['xfields'][$this->config['xfields']['write']['quality']] = $insert_data['quality'];

		if ($this->config['xfields']['write']['translation'] && $insert_data['translation'])
			$news->data['xfields'][$this->config['xfields']['write']['translation']] = $insert_data['translation'];

		if ($this->config['xfields']['write']['translations'] && $insert_data['translations'])
			$news->data['xfields'][$this->config['xfields']['write']['translations']] = $insert_data['translations'];

		if ($this->config['xfields']['write']['custom_quality'] && $insert_data['quality']) {
			$news->data['xfields'][$this->config['xfields']['write']['custom_quality']] = $insert_data['custom_quality'];
		}

		if ($this->config['xfields']['write']['custom_translation'] && $insert_data['custom_translation']) {
			$news->data['xfields'][$this->config['xfields']['write']['custom_translation']] = $insert_data['custom_translation'];
		}

		if ($this->config['xfields']['write']['custom_translations'] && $insert_data['custom_translations'])
			$news->data['xfields'][$this->config['xfields']['write']['custom_translations']] = $insert_data['custom_translations'];

		if ($this->config['xfields']['write']['title_rus'] && $insert_data['title_rus'])
			$news->data['xfields'][$this->config['xfields']['write']['title_rus']] = $insert_data['title_rus'];

		if ($this->config['xfields']['write']['title_orig'] && $insert_data['title_orig'])
			$news->data['xfields'][$this->config['xfields']['write']['title_orig']] = $insert_data['title_orig'];

		if ($this->config['xfields']['write']['slogan'] && $insert_data['slogan'])
			$news->data['xfields'][$this->config['xfields']['write']['slogan']] = $insert_data['slogan'];

		if ($this->config['xfields']['write']['description'] && $insert_data['description'])
			$news->data['xfields'][$this->config['xfields']['write']['description']] = $insert_data['description'];

		if ($this->config['xfields']['write']['year'] && $insert_data['year'])
			$news->data['xfields'][$this->config['xfields']['write']['year']] = $insert_data['year'];

		if ($this->config['xfields']['write']['duration'] && $insert_data['duration'])
			$news->data['xfields'][$this->config['xfields']['write']['duration']] = $insert_data['duration'];

		if ($this->config['xfields']['write']['genres'] && $insert_data['genres'])
			$news->data['xfields'][$this->config['xfields']['write']['genres']] = implode(', ', $insert_data['genres']);

		if ($this->config['xfields']['write']['countries'] && $insert_data['countries'])
			$news->data['xfields'][$this->config['xfields']['write']['countries']] = implode(', ', $insert_data['countries']);

		if ($this->config['xfields']['write']['age'] && $insert_data['age'])
			$news->data['xfields'][$this->config['xfields']['write']['age']] = $insert_data['age'];

		if ($this->config['xfields']['write']['poster'] && $insert_data['poster'])
			$news->data['xfields'][$this->config['xfields']['write']['poster']] = $insert_data['poster'];

		// Seo

		$insert_data['type'] = $data['content']['type'];

		if ($this->config['seo']['on']) {
			if ($this->config['seo']['url']) {
				$seo_url = $this->seo($insert_data, $this->config['seo']['url'], true);

				if ($seo_url)
					$news->data['alt_name'] = $seo_url;
			}

			if ($this->config['seo']['title']) {
				$seo_title = $this->seo($insert_data, $this->config['seo']['title']);

				if ($seo_title)
					$news->data['title'] = $seo_title;
			}

			if ($this->config['seo']['meta']['title']) {
				$seo_meta_title = $this->seo($insert_data, $this->config['seo']['meta']['title']);

				if ($seo_meta_title)
					$news->data['metatitle'] = $seo_meta_title;
			}

			if ($this->config['seo']['meta']['description']) {
				$seo_meta_description = $this->seo($insert_data, $this->config['seo']['meta']['description']);

				if ($seo_meta_description)
					$news->data['descr'] = $seo_meta_description;
			}
		}

		if (!$news->data['alt_name'])
			$news->data['alt_name'] = $this->seo($insert_data, '[title_rus]{title_rus}[/title_rus]', true);

		if (!$news->data['title'])
			$news->data['title'] = $this->seo($insert_data, '[title_rus]{title_rus}[/title_rus]');

		$post_id = $news->save();

		if ($data['content']['id'])
			$this->added[$data['content']['id']] = array_merge(['id' => $post_id], $news->data);

	}

	// Serials

	protected function serials($data)
	{

		global $db;

		if (!$data)
			return false;

		if ($this->first) {
			$end = end($data);
			$db->query("UPDATE " . PREFIX . "_cdnhub_update_log SET `update_id` = " . (intval($end['update_id']) ? intval($end['update_id']) : 0) . " WHERE `id` = 2");
			return false;
		}

		$row = $db->super_query("SELECT update_id FROM " . PREFIX . "_cdnhub_update_log WHERE id = 2");
		$last_update_id = $row['update_id'] ? $row['update_id'] : 0;

		krsort($data);

		$need_update = array();

		foreach ($data as $entry) {
			if ($entry['update_id'] > $last_update_id)
				$need_update[] = $entry;
		}

		if (!$need_update)
			return false;

		$updates = $this->search($need_update);

		foreach ($updates as $update) {
			if ($this->config['update']['serials']['on'] && ($update['post'] || $this->added[$update['content']['id']])) {
				if ($this->added[$update['content']['id']])
					$update['post'] = $this->added[$update['content']['id']];

				$this->serial_update($update);
			} else {
				if ($this->config['update']['serials']['add'])
					$this->serial_insert($update);
			}

			$db->query("UPDATE " . PREFIX . "_cdnhub_update_log SET `update_id` = " . intval($update['update_id']) . " WHERE `id` = 2");
		}

		return true;

	}

	protected function serial_update($data)
	{

		global $db;

		//

		$query = 'SELECT * FROM ' . PREFIX . "_post WHERE id = '{$data['post']['id']}'";
		$result = $db->query($query);
		$row = $db->get_row($result);
		$data['post'] = $row;

		//

		$news = new CDNHubNews($data['post']['id']);

		$news->config = $this->config;

		if (intval($data['post']['approve']))
			$news->approve = true;

		$xfields = $news->xfields->toArray($data['post']['xfields']);

		if ($this->config['xfields']['not_update'] && intval($xfields[$this->config['xfields']['not_update']]))
			return false;

		// Build

		$fields = array(
			
			'content|kinopoisk_id',
            'content|imdb_id',

            'content|iframe_url',

            'content|title_rus',
            'content|title_orig',
            'content|slogan',
            'content|description',
            'content|year',
            'content|duration',
            'content|genres',
            'content|countries',
            'content|age',
            'content|poster',
            
            'content|quality',
            'quality_custom',

            'content|season',
            'content|episode',
            
            'translation',
            'translation_custom',
            'translations',
            'translations_custom',

		);

		$update_data = array();

		foreach ($fields as $field) {
			if ($data[$field]) {
                $update_data[$field] = $data[$field];
            } elseif (strpos($field, '|') !== false) {
                list($lvl1, $lvl2) = explode('|', $field);

                if ($data[$lvl1][$lvl2])
                    $update_data[$lvl2] = $data[$lvl1][$lvl2];
            }
		}

		// Xfields

		$news->data['xfields'] = $xfields;

		$update_data['type'] = 'serial';

		if ($data['content']['translations'][0])
            $update_data['translation'] = $data['content']['translations'][0]['title'];
        else
            $update_data['translation'] = '';

        if ($this->config['xfields']['write']['custom_quality'] && $update_data['quality'])
            $update_data['custom_quality'] = $this->custom_replacement($update_data['quality'], $this->config['custom']['qualities']);
        else
            $update_data['custom_quality'] = '';


        if ($this->config['xfields']['write']['translation'] && $update_data['translation'])
            $update_data['custom_translation'] = $this->custom_replacement($update_data['translation'], $this->config['custom']['translations']);
        else
            $update_data['custom_translation'] = '';

        if (($this->config['xfields']['write']['translations'] || $this->config['xfields']['write']['custom_translations']) && $data['content']['translations']) {
            $_translations = [];
            $_custom_translations = [];
            foreach ($data['content']['translations'] as $_translation) {
                $_translations[] = $_translation['title'];
                $_custom_translations[] = $this->custom_replacement($_translation['title'], $this->config['custom']['translations']);
            }
            $update_data['translations'] = implode(', ', $_translations);
            $update_data['custom_translations'] = implode(', ', $_custom_translations);
        } else {
            $update_data['translations'] = '';
            $update_data['custom_translations'] = '';
        }

        if ($update_data['iframe_url'] && $xfields[$this->config['xfields']['write']['source']])
            $update_data['iframe_url'] = $this->save_source_params($update_data['iframe_url'], $xfields[$this->config['xfields']['write']['source']]);

        // News

        if ($this->config['xfields']['write']['season'] && $update_data['season'] && $update_data['season'] != $xfields[$this->config['xfields']['write']['season']]) {
            $news->data['xfields'][$this->config['xfields']['write']['season']] = $update_data['season'];

            if ($this->config['xfields']['write']['format_season'] && $this->config['xfields']['write']['format_season_type']) {
				$update_data['format_season'] = $this->format_season($this->config['xfields']['write']['format_season_type'], $update_data['season']);
				$news->data['xfields'][$this->config['xfields']['write']['format_season']] = $update_data['format_season'];
            }
        }

        if ($this->config['xfields']['write']['episode'] && $update_data['episode'] && $update_data['episode'] != $xfields[$this->config['xfields']['write']['episode']]) {
            $news->data['xfields'][$this->config['xfields']['write']['episode']] = $update_data['episode'];
            
            if ($this->config['xfields']['write']['format_episode'] && $this->config['xfields']['write']['format_episode_type']) {
				$update_data['format_episode'] = $this->format_episode($this->config['xfields']['write']['format_episode_type'], $update_data['episode']);
				$news->data['xfields'][$this->config['xfields']['write']['format_episode']] = $update_data['format_episode'];
            }
        }

        // if ($this->config['update']['serials']['up'] && intval($data['post']['approve']) && strtotime($data['post']['date']) <= time()) {
        if ($this->config['update']['serials']['up']) {
			if ($update_data['season'] && $xfields[$this->config['xfields']['write']['season']] && $update_data['episode'] && $xfields[$this->config['xfields']['write']['episode']]) {
				if ($update_data['season'] > $xfields[$this->config['xfields']['write']['season']] || ($update_data['season'] == $xfields[$this->config['xfields']['write']['season']] && intval($update_data['episode']) > intval($xfields[$this->config['xfields']['write']['episode']]))) {
					$news->data['date'] = date('Y-m-d H:i:s', time());

					//

					if ($this->config['xfields']['write']['season'] && $update_data['season']) {
						$news->data['xfields'][$this->config['xfields']['write']['season']] = $update_data['season'];

			            if ($this->config['xfields']['write']['format_season'] && $this->config['xfields']['write']['format_season_type']) {
							$update_data['format_season'] = $this->format_season($this->config['xfields']['write']['format_season_type'], $update_data['season']);
							$news->data['xfields'][$this->config['xfields']['write']['format_season']] = $update_data['format_season'];
			            }
					}

					// Seo

					if ($this->config['seo']['on']) {
						if ($this->config['seo']['url']) {
							$seo_url = $this->seo($update_data, $this->config['seo']['url'], true);

							if ($seo_url != $data['post']['alt_name'])
								$news->data['alt_name'] = $seo_url;
						}

						if ($this->config['seo']['title']) {
							$seo_title = $this->seo($update_data, $this->config['seo']['title']);

							if ($seo_title != $data['post']['title'])
								$news->data['title'] = $seo_title;
						}

						if ($this->config['seo']['meta']['title']) {
							$seo_meta_title = $this->seo($update_data, $this->config['seo']['meta']['title']);

							if ($seo_meta_title != $data['post']['metatitle'])
								$news->data['metatitle'] = $seo_meta_title;
						}

						if ($this->config['seo']['meta']['description']) {
							$seo_meta_description = $this->seo($update_data, $this->config['seo']['meta']['description']);

							if ($seo_meta_description != $data['post']['descr'])
								$news->data['descr'] = $seo_meta_description;
						}
					}
				}
			}
		}

        if ($this->config['xfields']['write']['source'] && $update_data['iframe_url'] && $update_data['iframe_url'] != $xfields[$this->config['xfields']['write']['source']])
            $news->data['xfields'][$this->config['xfields']['write']['source']] = $update_data['iframe_url'];

        if ($this->config['xfields']['write']['quality'] && $update_data['quality'] && $update_data['quality'] != $xfields[$this->config['xfields']['write']['quality']])
            $news->data['xfields'][$this->config['xfields']['write']['quality']] = $update_data['quality'];

        if ($this->config['xfields']['write']['custom_quality'] && $update_data['quality']) {
            $update_data['custom_quality'] = $this->custom_replacement($update_data['quality'], $this->config['custom']['qualities']);

            if ($update_data['custom_quality'] != $xfields[$this->config['xfields']['write']['custom_quality']])
                $news->data['xfields'][$this->config['xfields']['write']['custom_quality']] = $update_data['custom_quality'];
        }

        if ($this->config['xfields']['write']['custom_translation'] && $update_data['translation']) {
            $update_data['custom_translation'] = $this->custom_replacement($update_data['translation'], $this->config['custom']['translations']);

            if ($update_data['custom_translation'] != $xfields[$this->config['xfields']['write']['custom_translation']])
                $news->data['xfields'][$this->config['xfields']['write']['custom_translation']] = $update_data['custom_translation'];
        }

        if ($this->config['xfields']['write']['translation'] && $update_data['translation'] && $update_data['translation'] != $xfields[$this->config['xfields']['write']['translation']])
            $news->data['xfields'][$this->config['xfields']['write']['translation']] = $update_data['translation'];

		if ($news->data['xfields'] == $xfields)
			unset($news->data['xfields']);

		$news->save();

		$this->added[$data['content']['id']] = array_merge(['id' => $data['post']['id']], $news->data);

		$post_id = $data['post']['id'];

		// Updates

		if ($post_id && $this->config['serials']['updates']['on']) {
            $clear_cache = false;

            $token = '';
            $translation_id = $db->safesql($data['translation']['id']);
            $quality = $db->safesql($data['content']['quality']);
            $season = intval($data['season']);
            $episode = intval($data['episode']);

            $date = date('Y-m-d H:i:s', time());

            $result = $db->query("SELECT * FROM " . PREFIX . "_cdnhub_update_serials WHERE `post_id` = '{$post_id}' AND `season` = '{$season}' AND `episode` = '{$episode}'");

            if (!$db->num_rows($result)) {
                $db->query("INSERT INTO " . PREFIX . "_cdnhub_update_serials (`post_id`, `token`, `update_date`, `translation_id`, `quality`, `season`, `episode`) VALUES ('{$post_id}', '{$token}', '{$date}', '{$translation_id}', '{$quality}', '{$season}', '{$episode}')");

                $clear_cache = true;
            }

            if ($clear_cache)
                clear_cache(array('cdnhub_updates'));
        }

	}

	public function serial_insert($data)
	{

		global $db;

		$news = new CDNHubNews;

		$news->config = $this->config;

		// Build

		if (!isset($data['content']))
			$data = ['content' => $data];

		$fields = array(
			
			'content|kinopoisk_id',
            'content|imdb_id',

            'content|iframe_url',

            'content|title_rus',
            'content|title_orig',
            'content|slogan',
            'content|description',
            'content|year',
            'content|duration',
            'content|genres',
            'content|countries',
            'content|age',
            'content|poster',
            
            'content|quality',

            'content|season',
            'content|episode',

		);

		$insert_data = array();

		foreach ($fields as $field) {
			if ($data[$field]) {
                $insert_data[$field] = $data[$field];
            } elseif (strpos($field, '|') !== false) {
                list($lvl1, $lvl2) = explode('|', $field);

                if ($data[$lvl1][$lvl2])
                    $insert_data[$lvl2] = $data[$lvl1][$lvl2];
            }
		}

		// Xfields

		if ($data['content']['translations'][0]['title'])
            $insert_data['translation'] = $data['content']['translations'][0]['title'];
        else
            $insert_data['translation'] = '';

        if ($this->config['xfields']['write']['custom_quality'] && $insert_data['quality'])
            $insert_data['custom_quality'] = $this->custom_replacement($insert_data['quality'], $this->config['custom']['qualities']);
        else
            $insert_data['custom_quality'] = '';


        if ($this->config['xfields']['write']['translation'] && $insert_data['translation'])
            $insert_data['custom_translation'] = $this->custom_replacement($insert_data['translation'], $this->config['custom']['translations']);
        else
            $insert_data['custom_translation'] = '';

        if (($this->config['xfields']['write']['translations'] || $this->config['xfields']['write']['custom_translations']) && $data['content']['translations']) {
            $_translations = [];
            $_custom_translations = [];
            foreach ($data['content']['translations'] as $_translation) {
                $_translations[] = $_translation['title'];
                $_custom_translations[] = $this->custom_replacement($_translation['title'], $this->config['custom']['translations']);
            }
            $insert_data['translations'] = implode(', ', $_translations);
            $insert_data['custom_translations'] = implode(', ', $_custom_translations);
        } else {
            $insert_data['translations'] = '';
            $insert_data['custom_translations'] = '';
        }

        // Episode

        if ($this->config['xfields']['write']['season'] && $insert_data['season']) {
            $news->data['xfields'][$this->config['xfields']['write']['season']] = $insert_data['season'];

            if ($this->config['xfields']['write']['format_season'] && $this->config['xfields']['write']['format_season_type']) {
				$insert_data['format_season'] = $this->format_season($this->config['xfields']['write']['format_season_type'], $insert_data['season']);
				$news->data['xfields'][$this->config['xfields']['write']['format_season']] = $insert_data['format_season'];
            }
        }

        if ($this->config['xfields']['write']['episode'] && $insert_data['episode']) {
            $news->data['xfields'][$this->config['xfields']['write']['episode']] = $insert_data['episode'];

            if ($this->config['xfields']['write']['format_episode'] && $this->config['xfields']['write']['format_episode_type']) {
				$insert_data['format_episode'] = $this->format_episode($this->config['xfields']['write']['format_episode_type'], $insert_data['episode']);
				$news->data['xfields'][$this->config['xfields']['write']['format_episode']] = $insert_data['format_episode'];
            }
        }

        // News

		if ($this->config['xfields']['search']['kinopoisk_id'] && $insert_data['kinopoisk_id'])
            $news->data['xfields'][$this->config['xfields']['search']['kinopoisk_id']] = $insert_data['kinopoisk_id'];

        if ($this->config['xfields']['search']['imdb_id'] && $insert_data['imdb_id'])
            $news->data['xfields'][$this->config['xfields']['search']['imdb_id']] = $insert_data['imdb_id'];

        if ($this->config['xfields']['write']['source'] && $insert_data['iframe_url'])
            $news->data['xfields'][$this->config['xfields']['write']['source']] = $insert_data['iframe_url'];

        if ($this->config['xfields']['write']['quality'] && $insert_data['quality'])
            $news->data['xfields'][$this->config['xfields']['write']['quality']] = $insert_data['quality'];

        if ($this->config['xfields']['write']['translation'] && $insert_data['translation'])
            $news->data['xfields'][$this->config['xfields']['write']['translation']] = $insert_data['translation'];

        if ($this->config['xfields']['write']['translations'] && $insert_data['translations'])
            $news->data['xfields'][$this->config['xfields']['write']['translations']] = $insert_data['translations'];

        if ($this->config['xfields']['write']['custom_quality'] && $insert_data['quality']) {
            $news->data['xfields'][$this->config['xfields']['write']['custom_quality']] = $insert_data['custom_quality'];
        }

        if ($this->config['xfields']['write']['custom_translation'] && $insert_data['custom_translation']) {
            $news->data['xfields'][$this->config['xfields']['write']['custom_translation']] = $insert_data['custom_translation'];
        }

        if ($this->config['xfields']['write']['custom_translations'] && $insert_data['custom_translations'])
            $news->data['xfields'][$this->config['xfields']['write']['custom_translations']] = $insert_data['custom_translations'];

        if ($this->config['xfields']['write']['title_rus'] && $insert_data['title_rus'])
            $news->data['xfields'][$this->config['xfields']['write']['title_rus']] = $insert_data['title_rus'];

        if ($this->config['xfields']['write']['title_orig'] && $insert_data['title_orig'])
            $news->data['xfields'][$this->config['xfields']['write']['title_orig']] = $insert_data['title_orig'];

        if ($this->config['xfields']['write']['slogan'] && $insert_data['slogan'])
            $news->data['xfields'][$this->config['xfields']['write']['slogan']] = $insert_data['slogan'];

        if ($this->config['xfields']['write']['description'] && $insert_data['description'])
            $news->data['xfields'][$this->config['xfields']['write']['description']] = $insert_data['description'];

        if ($this->config['xfields']['write']['year'] && $insert_data['year'])
            $news->data['xfields'][$this->config['xfields']['write']['year']] = $insert_data['year'];

        if ($this->config['xfields']['write']['duration'] && $insert_data['duration'])
            $news->data['xfields'][$this->config['xfields']['write']['duration']] = $insert_data['duration'];

        if ($this->config['xfields']['write']['genres'] && $insert_data['genres'])
            $news->data['xfields'][$this->config['xfields']['write']['genres']] = implode(', ', $insert_data['genres']);

        if ($this->config['xfields']['write']['countries'] && $insert_data['countries'])
            $news->data['xfields'][$this->config['xfields']['write']['countries']] = implode(', ', $insert_data['countries']);

        if ($this->config['xfields']['write']['age'] && $insert_data['age'])
            $news->data['xfields'][$this->config['xfields']['write']['age']] = $insert_data['age'];

        if ($this->config['xfields']['write']['poster'] && $insert_data['poster'])
            $news->data['xfields'][$this->config['xfields']['write']['poster']] = $insert_data['poster'];

		// Seo

		$insert_data['type'] = 'serial';

		if ($this->config['seo']['on']) {
			if ($this->config['seo']['url']) {
				$seo_url = $this->seo($insert_data, $this->config['seo']['url'], true);

				if ($seo_url != $data['post']['alt_name'])
					$news->data['alt_name'] = $seo_url;
			}

			if ($this->config['seo']['title']) {
				$seo_title = $this->seo($insert_data, $this->config['seo']['title']);

				if ($seo_title != $data['post']['title'])
					$news->data['title'] = $seo_title;
			}

			if ($this->config['seo']['meta']['title']) {
				$seo_meta_title = $this->seo($insert_data, $this->config['seo']['meta']['title']);

				if ($seo_meta_title != $data['post']['metatitle'])
					$news->data['metatitle'] = $seo_meta_title;
			}

			if ($this->config['seo']['meta']['description']) {
				$seo_meta_description = $this->seo($insert_data, $this->config['seo']['meta']['description']);

				if ($seo_meta_description != $data['post']['descr'])
					$news->data['descr'] = $seo_meta_description;
			}
		}

		if (!$news->data['alt_name'])
			$news->data['alt_name'] = $this->seo($insert_data, '[title_ru]{title_ru}[/title_ru]', true);

		if (!$news->data['title'])
			$news->data['title'] = $this->seo($insert_data, '[title_ru]{title_ru}[/title_ru]');

		$post_id = $news->save();

		if (isset($data['content']['id']))
			$this->added[$data['content']['id']] = array_merge(['id' => $post_id], $news->data);

		// Updates

		if ($post_id && $this->config['serials']['updates']['on'] && isset($data['content']['id'])) {
			$clear_cache = false;

			$token = '';
			$translation_id = $db->safesql($data['translation']['id']);
			$quality = $db->safesql($data['content']['quality']);
			$season = intval($data['season']);
			$episode = intval($data['episode']);

			$date = date('Y-m-d H:i:s', time());

			$result = $db->query("SELECT * FROM " . PREFIX . "_cdnhub_update_serials WHERE `post_id` = '{$post_id}' AND `season` = '{$season}' AND `episode` = '{$episode}'");

			if (!$db->num_rows($result)) {
				$db->query("INSERT INTO " . PREFIX . "_cdnhub_update_serials (`post_id`, `token`, `update_date`, `translation_id`, `quality`, `season`, `episode`) VALUES ('{$post_id}', '{$token}', '{$date}', '{$translation_id}', '{$quality}', '{$season}', '{$episode}')");

				$clear_cache = true;
			}

			if ($clear_cache)
				clear_cache(array('cdnhub_updates'));
		}

	}

	// Seo

	public function seo($data, $template, $translit = false)
	{

		if ($data['type'] == 'movie') {
			$template = preg_replace("#\\[movie\\](.*?)\\[/movie\\]#i", "$1", $template);
			$template = preg_replace('#\\[serial\\].*?\\[/serial\\]#i', '', $template);
		} else {
			$template = preg_replace("#\\[serial\\](.*?)\\[/serial\\]#i", "$1", $template);
			$template = preg_replace('#\\[movie\\].*?\\[/movie\\]#i', '', $template);
		}

		$fields = array(
			
			'year',
			
			'quality',
			'translation',

			'custom_quality',
			'custom_translation',

			'season',
			'episode',

			'format_season',
			'format_episode',

			'title_rus',
			'title_orig'

		);

		foreach ($fields as $field) {
			if ($data[$field]) {
				$template = str_replace('{' . $field . '}', $data[$field], $template);
				$template = preg_replace("#\\[{$field}\\](.*?)\\[/{$field}\\]#i", "$1", $template);
			} else {
				$template = str_replace('{' . $field . '}', '', $template);
				$template = preg_replace("#\\[{$field}\\].*?\\[/{$field}\\]#i", '', $template);
			}
		}

		if ($translit) {
			$template = trim($template);

			$replaces = array(
				'а' => 'a',
				'б' => 'b',
				'в' => 'v',
				'г' => 'g',
				'д' => 'd',
				'е' => 'e',
				'ё' => 'yo',
				'ж' => 'zh',
				'з' => 'z',
				'и' => 'i',
				'й' => 'y',
				'к' => 'k',
				'л' => 'l',
				'м' => 'm',
				'н' => 'n',
				'о' => 'o',
				'п' => 'p',
				'р' => 'r',
				'с' => 's',
				'т' => 't',
				'у' => 'u',
				'ф' => 'f',
				'х' => 'kh',
				'ц' => 'ts',
				'ч' => 'ch',
				'ш' => 'sh',
				'щ' => 'shch',
				'ь' => '',
				'ы' => 'y',
				'ъ' => '',
				'э' => 'e',
				'ю' => 'yu',
				'я' => 'ya'
			);

			$template = mb_strtolower($template, mb_detect_encoding($template, 'utf-8'));
			$template = strtr($template, $replaces);
			$template = preg_replace("#[^-a-z0-9]+#i", '-', $template);
			$template = trim($template, '-');
		}

		return $template;

	}

	// Custom Replacements

	public function custom_replacement($string, $replacements)
	{

		if ($replacements) foreach ($replacements as $pattern => $replacement) {
			if (trim($string) == trim($pattern)) {
				$string = str_ireplace($pattern, $replacement, $string);
				break;
			}
		}

		return $string;

	}

	// Format Season

	public function format_season($format, $season)
	{

		switch ($format) {

			case 1:

				$data = "{$season} сезон";

				break;

			case 2:

				$data = $season > 1 ? "1-{$season} сезон" : "{$season} сезон";

				break;

			case 3:

				$data = array();

				for ($i = 1; $i <= $season; $i++)
					$data[] = $i;

				$data = implode(',', $data) . ' сезон';

				break;

		}

		return $data;

	}

	// Format Episode

	public function format_episode($format, $episode)
	{

		switch ($format) {

			case 1:

				$data = "{$episode} серия";

				break;

			case 2:

				$data = $episode > 1 ? "1-{$episode} серия" : "{$episode} серия";

				break;

			case 3:

				$data = array();

				for ($i = 1; $i <= $episode; $i++)
					$data[] = $i;

				$data = implode(',', $data) . ' серия';

				break;

			case 4:

				$data = array();

				$start = $episode - 2;

				if ($start < 1)
					$start = 1;

				for ($i = $start; $i <= $episode; $i++)
					$data[] = $i;

				$data = implode(',', $data) . ' серия';

				break;

			case 5:

				if ($episode > 5) {

					$data = array();

					$start = $episode - 2;

					if ($start < 5)
						$start = 5;

					for ($i = $start; $i <= $episode; $i++)
						$data[] = $i;

					$data = '1-' . implode(',', $data) . ' серия';

				} else {

					$data = array();

					for ($i = 1; $i <= $episode; $i++)
						$data[] = $i;

					$data = implode(',', $data) . ' серия';

				}

				break;

		}

		return $data;

	}

	// Save Source Params

	protected function save_source_params($new_source, $source)
	{
		
		$url = parse_url($source);

		if ($url['query']) {
			if (strpos($new_source, '?') === false)
				$new_source .= "?{$url['query']}";
			else
				$new_source .= "&{$url['query']}";
		}

		return $new_source;

	}

}