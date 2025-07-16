<?php

class CDNHubReplacement
{

	protected $config;

	public function __construct($config)
	{

		$this->config = $config;

	}

	// Threads

	public function threads()
	{

		global $db;

		$search = array();

		if ($_POST['replacement']['search'])
			foreach ($_POST['replacement']['search'] as $key => $value) {
				if ($this->config['xfields']['search'] && $this->config['xfields']['search'][$key])
					$search[] = $key;
			}

		if (!$search)
			exit;

		$threads = intval($_POST['replacement']['threads']) ? intval($_POST['replacement']['threads']) : null;

		if ($_GET['category']) {
			$list = explode(',', $_GET['category']);

			$category = array();

			foreach ($list as $key => $value) {
				if (intval($value) > 0)
					$category[] = intval($value);
			}
		}

		$category_inverse = isset($_POST['replacement']['category_inverse']) ? true : false;

		$status = intval($_POST['replacement']['status']) ? intval($_POST['replacement']['status']) : null;

		$last_post_id = intval($_GET['post_id']) ? intval($_GET['post_id']) : null;

		// Query

		if ($status == 1)
			$approve = 'approve = 1';
		elseif ($status == 2)
			$approve = 'approve = 0';

		if ($category) {
			$searchcategory = array();

			foreach ($category as $category_id)
				$searchcategory[] = get_sub_cats($category_id);

			$searchcategory = implode('|', $searchcategory);

			if ($searchcategory)
				$searchcategory = "category" . ($category_inverse ? ' not' : '') . " regexp '[[:<:]]($searchcategory)[[:>:]]'";
		}

		if ($last_post_id) {
			$next_posts_id = array();
			$result = $db->query("SELECT id FROM " . PREFIX . "_post WHERE id > {$last_post_id}" . ($searchcategory ? ' AND ' . $searchcategory : '') . ($approve ? ' AND ' . $approve : '') . " ORDER BY id ASC LIMIT {$threads}");
			while ($row = $db->get_row($result)) {
				$next_posts_id[] = $row['id'];
			}
			
			if ($next_posts_id) {
				$result = array(
					'status' => 'success',
					'next_posts_id' => $next_posts_id,
				);
			} else
				$result = array(
					'status' => 'end',
				);
		} else {
			$post = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE id <> 0 " . ($searchcategory ? ' AND ' . $searchcategory : '') . ($approve ? ' AND ' . $approve : ''));

			$count = $post['count'];

			if ($count) {
				$next_posts_id = array();
				$result = $db->query("SELECT id FROM " . PREFIX . "_post WHERE id <> 0 " . ($searchcategory ? ' AND ' . $searchcategory : '') . ($approve ? ' AND ' . $approve : '') . " ORDER BY id ASC LIMIT {$threads}");
				while ($row = $db->get_row($result)) {
					$next_posts_id[] = $row['id'];
				}
				
				if ($next_posts_id) {
					$result = array(
						'status' => 'success',
						'next_posts_id' => $next_posts_id,
						'count' => $count,
					);
				} else
					$result = array(
						'status' => 'end',
					);
			} else
				$result = array(
					'status' => 'end',
					'code' => '#0',
				);
		}

		return $result;

	}

	// Thread

	public function thread()
	{

		global $db;

		$search = array();

		if ($_POST['replacement']['search'])
			foreach ($_POST['replacement']['search'] as $key => $value) {
				if ($this->config['xfields']['search'] && $this->config['xfields']['search'][$key])
					$search[] = $key;
			}

		if (!$search)
			return false;

		$rewrite = isset($_POST['replacement']['rewrite']) ? true : false;

		$post_id = intval($_GET['post_id']) ? intval($_GET['post_id']) : null;

		if (!$post_id) {
			return array(
				'status' => 'error',
				'code' => '#1',
			);
		}

		$post = $db->super_query("SELECT * FROM " . PREFIX . "_post WHERE id = '{$post_id}'");

		if (!$post) {
			return array(
				'status' => 'error',
				'code' => '#1',
			);
		}
		
		$news = new CDNHubNews($post_id);
		$update = new CDNHubUpdate($this->config);

		$xfields = $news->xfields->toArray($post['xfields']);

		if (!$rewrite && $xfields[$this->config['xfields']['write']['source']])
			return array('status' => 'exist');

		$search = array();

		foreach ($update->search as $value) {
			if (!$this->config['xfields']['search'][$value] || !$xfields[$this->config['xfields']['search'][$value]])
				continue;

			$search[$value] = $xfields[$this->config['xfields']['search'][$value]];
		}

		if (!$search)
			return array(
				'status' => 'error',
				'code' => '#3',
			);

		$api = new CDNHubApi($this->config['api']);

		foreach ($search as $key => $value) {
			$data = $api->search($key, $value);

			if ($data)
				break;
		}

		if (!$data)
			return array(
				'status' => 'error',
				'code' => '#4',
			);

		// Build

		$fields = array(
			
			'kinopoisk_id',
			'imdb_id',

			'iframe_url',

			'title_rus',
			'title_orig',
			'slogan',
			'description',
			'year',
			'duration',
			'genres',
			'countries',
			'age',
			'poster',
			
			'quality',
			'translation',
			'translations',

			'custom_quality',
			'custom_translation',
			'custom_translations',

			'season',
			'episode',

			'format_season',
			'format_episode',

		);

		$post_data = array();
		$update_data = array();

		$data = $data[0];

		foreach ($fields as $field) {
			if ($this->config['xfields']['search'][$field] && $xfields[$this->config['xfields']['search'][$field]])
				$post_data[$field] = $xfields[$this->config['xfields']['search'][$field]];

			if ($this->config['xfields']['write'][$field] && $xfields[$this->config['xfields']['write'][$field]])
				$post_data[$field] = $xfields[$this->config['xfields']['write'][$field]];

			if ($data[$field])
				$update_data[$field] = $data[$field];
		}



		if ($data['quality'])
			$update_data['quality'] = $data['quality'];
		else
			$update_data['quality'] = '';

		if ($this->config['xfields']['write']['custom_quality'] && $data['quality'])
			$update_data['custom_quality'] = $update->custom_replacement($data['quality'], $this->config['custom']['qualities']);
		else
			$update_data['custom_quality'] = '';



		if ($data['translations'][0])
			$update_data['translation'] = $data['translations'][0]['title'];
		else
			$update_data['translation'] = '';

		if ($this->config['xfields']['write']['translation'] && $data['translations'][0])
			$update_data['custom_translation'] = $update->custom_replacement($data['translations'][0]['title'], $this->config['custom']['translations']);
		else
			$update_data['custom_translation'] = '';

		if (($this->config['xfields']['write']['translations'] || $this->config['xfields']['write']['custom_translations']) && $data['translations']) {
			$_translations = [];
			$_custom_translations = [];
			foreach ($data['translations'] as $_translation) {
				$_translations[] = $_translation['title'];
				$_custom_translations[] = $update->custom_replacement($_translation['title'], $this->config['custom']['translations']);
			}
			$update_data['translations'] = implode(', ', $_translations);
			$update_data['custom_translations'] = implode(', ', $_custom_translations);
		} else {
			$update_data['translations'] = '';
			$update_data['custom_translations'] = '';
		}



		if ($data['type'] == 'serial') {
			
			$update_data['format_season'] = '';
			$update_data['format_episode'] = '';

			$update_season = $data['season'];
			$update_episode = $data['episode'];

			if ($update_season) {
				$update_data['season'] = $update_season;

				if ($this->config['xfields']['write']['format_season'] && $this->config['xfields']['write']['format_season_type'])
					$update_data['format_season'] = $update->format_season($this->config['xfields']['write']['format_season_type'], $update_season);
			}

			if ($update_episode) {
				$update_data['episode'] = $update_episode;

				if ($this->config['xfields']['write']['format_episode'] && $this->config['xfields']['write']['format_episode_type'])
					$update_data['format_episode'] = $update->format_episode($this->config['xfields']['write']['format_episode_type'], $update_episode);
			}

		}



		$news->data['xfields'] = $xfields;



		if ($this->config['xfields']['search']['kinopoisk_id'] && $update_data['kinopoisk_id'] && $update_data['kinopoisk_id'] != $post_data['kinopoisk_id'])
			$news->data['xfields'][$this->config['xfields']['search']['kinopoisk_id']] = $update_data['kinopoisk_id'];

		if ($this->config['xfields']['search']['imdb_id'] && $update_data['imdb_id'] && $update_data['imdb_id'] != $post_data['imdb_id'])
			$news->data['xfields'][$this->config['xfields']['search']['imdb_id']] = $update_data['imdb_id'];



		if ($_POST['replacement']['xfields']['source'] && $this->config['xfields']['write']['source'] && $update_data['iframe_url'] && $update_data['iframe_url'] != $post_data['iframe_url'])
			$news->data['xfields'][$this->config['xfields']['write']['source']] = $update_data['iframe_url'];



		if ($_POST['replacement']['xfields']['quality'] && $this->config['xfields']['write']['quality'] && $update_data['quality'] && $update_data['quality'] != $post_data['quality'])
			$news->data['xfields'][$this->config['xfields']['write']['quality']] = $update_data['quality'];

		if ($_POST['replacement']['xfields']['translation'] && $this->config['xfields']['write']['translation'] && $update_data['translation'] && $update_data['translation'] != $post_data['translation'])
			$news->data['xfields'][$this->config['xfields']['write']['translation']] = $update_data['translation'];

		if ($_POST['replacement']['xfields']['translations'] && $this->config['xfields']['write']['translations'] && $update_data['translations'] && $update_data['translations'] != $post_data['translations'])
			$news->data['xfields'][$this->config['xfields']['write']['translations']] = $update_data['translations'];

		if ($_POST['replacement']['xfields']['custom_quality'] && $this->config['xfields']['write']['custom_quality'] && $update_data['quality']) {
			if ($update_data['custom_quality'] != $post_data['custom_quality'])
				$news->data['xfields'][$this->config['xfields']['write']['custom_quality']] = $update_data['custom_quality'];
		}

		if ($_POST['replacement']['xfields']['custom_translation'] && $this->config['xfields']['write']['custom_translation'] && $update_data['custom_translation']) {
			if ($update_data['custom_translation'] != $post_data['custom_translation'])
				$news->data['xfields'][$this->config['xfields']['write']['custom_translation']] = $update_data['custom_translation'];
		}

		if ($_POST['replacement']['xfields']['custom_translations'] && $this->config['xfields']['write']['custom_translations'] && $update_data['custom_translations'] && $update_data['custom_translations'] != $post_data['custom_translations'])
			$news->data['xfields'][$this->config['xfields']['write']['custom_translations']] = $update_data['custom_translations'];

		if ($_POST['replacement']['xfields']['season'] && $this->config['xfields']['write']['season'] && $update_data['season'] && $update_data['season'] != $post_data['season'])
			$news->data['xfields'][$this->config['xfields']['write']['season']] = $update_data['season'];

		if ($_POST['replacement']['xfields']['episode'] && $this->config['xfields']['write']['episode'] && $update_data['episode'] && $update_data['episode'] != $post_data['episode'])
			$news->data['xfields'][$this->config['xfields']['write']['episode']] = $update_data['episode'];

		if ($_POST['replacement']['xfields']['format_season'] && $this->config['xfields']['write']['format_season'] && $update_data['format_season'] && $update_data['format_season'] != $post_data['format_season'])
			$news->data['xfields'][$this->config['xfields']['write']['format_season']] = $update_data['format_season'];

		if ($_POST['replacement']['xfields']['format_episode'] && $this->config['xfields']['write']['format_episode'] && $update_data['format_episode'] && $update_data['format_episode'] != $post_data['format_episode'])
			$news->data['xfields'][$this->config['xfields']['write']['format_episode']] = $update_data['format_episode'];



		if ($_POST['replacement']['xfields']['title_rus'] && $this->config['xfields']['write']['title_rus'] && $update_data['title_rus'] && $update_data['title_rus'] != $post_data['title_rus'])
			$news->data['xfields'][$this->config['xfields']['write']['title_rus']] = $update_data['title_rus'];

		if ($_POST['replacement']['xfields']['title_orig'] && $this->config['xfields']['write']['title_orig'] && $update_data['title_orig'] && $update_data['title_orig'] != $post_data['title_orig'])
			$news->data['xfields'][$this->config['xfields']['write']['title_orig']] = $update_data['title_orig'];

		if ($_POST['replacement']['xfields']['slogan'] && $this->config['xfields']['write']['slogan'] && $update_data['slogan'] && $update_data['slogan'] != $post_data['slogan'])
			$news->data['xfields'][$this->config['xfields']['write']['slogan']] = $update_data['slogan'];

		if ($_POST['replacement']['xfields']['description'] && $this->config['xfields']['write']['description'] && $update_data['description'] && $update_data['description'] != $post_data['description'])
			$news->data['xfields'][$this->config['xfields']['write']['description']] = $update_data['description'];

		if ($_POST['replacement']['xfields']['year'] && $this->config['xfields']['write']['year'] && $update_data['year'] && $update_data['year'] != $post_data['year'])
			$news->data['xfields'][$this->config['xfields']['write']['year']] = $update_data['year'];

		if ($_POST['replacement']['xfields']['duration'] && $this->config['xfields']['write']['duration'] && $update_data['duration'] && $update_data['duration'] != $post_data['duration'])
			$news->data['xfields'][$this->config['xfields']['write']['duration']] = $update_data['duration'];

		if ($_POST['replacement']['xfields']['genres'] && $this->config['xfields']['write']['genres'] && $update_data['genres'] && $update_data['genres'] != $post_data['genres'])
			$news->data['xfields'][$this->config['xfields']['write']['genres']] = implode(', ', $update_data['genres']);

		if ($_POST['replacement']['xfields']['countries'] && $this->config['xfields']['write']['countries'] && $update_data['countries'] && $update_data['countries'] != $post_data['countries'])
			$news->data['xfields'][$this->config['xfields']['write']['countries']] = implode(', ', $update_data['countries']);

		if ($_POST['replacement']['xfields']['age'] && $this->config['xfields']['write']['age'] && $update_data['age'] && $update_data['age'] != $post_data['age'])
			$news->data['xfields'][$this->config['xfields']['write']['age']] = $update_data['age'];

		if ($_POST['replacement']['xfields']['poster'] && $this->config['xfields']['write']['poster'] && $update_data['poster'] && $update_data['poster'] != $post_data['poster'])
			$news->data['xfields'][$this->config['xfields']['write']['poster']] = $update_data['poster'];




		if ($news->data['xfields'] == $xfields)
			unset($news->data['xfields']);

		// Seo

		if ($this->config['seo']['on']) {
			if ($_POST['replacement']['seo']['url'] && $this->config['seo']['url']) {
				$seo_url = $update->seo($update_data, $this->config['seo']['url'], true);

				if ($seo_url != $data['post']['alt_name'])
					$news->data['alt_name'] = $seo_url;
			}

			if ($_POST['replacement']['seo']['title'] && $this->config['seo']['title']) {
				$seo_title = $update->seo($update_data, $this->config['seo']['title']);

				if ($seo_title != $data['post']['title'])
					$news->data['title'] = $seo_title;
			}

			if ($_POST['replacement']['seo']['meta_title'] && $this->config['seo']['meta']['title']) {
				$seo_meta_title = $update->seo($update_data, $this->config['seo']['meta']['title']);

				if ($seo_meta_title != $data['post']['metatitle'])
					$news->data['metatitle'] = $seo_meta_title;
			}

			if ($_POST['replacement']['seo']['meta_description'] && $this->config['seo']['meta']['description']) {
				$seo_meta_description = $update->seo($update_data, $this->config['seo']['meta']['description']);

				if ($seo_meta_description != $data['post']['descr'])
					$news->data['descr'] = $seo_meta_description;
			}
		}

		$news->save();

		return array('status' => 'success');

	}

}