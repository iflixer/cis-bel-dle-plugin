<?php

global $config, $db, $cdnhub;

if (!$cdnhub->config['serials']['updates']['on'])
	return false;

$month = array(
	1 => 'января',
	2 => 'февраля',
	3 => 'марта',
	4 => 'апреля',
	5 => 'мая',
	6 => 'июня',
	7 => 'июля',
	8 => 'августа',
	9 => 'сентября',
	10 => 'октября',
	11 => 'ноября',
	12 => 'декабря'
);

$days = intval($cdnhub->config['serials']['updates']['days']) ? intval($cdnhub->config['serials']['updates']['days']) : 7;
$limit = intval($cdnhub->config['serials']['updates']['items']) ? intval($cdnhub->config['serials']['updates']['items']) : false;

$template = VH_DIR . '/widgets/updates.tpl';

if (!file_exists($template)) {
	echo "Template not found: cdnhub/widgets/updates.tpl";
	return false;
}

$allow_cache = $config['allow_cache'];
$clear_cache = $config['clear_cache'];
$cache_type = $config['cache_type'];

$config['allow_cache'] = 1;
$config['clear_cache'] = (60 * 24);
$config['cache_type'] = 0;

$cache_hash = 'cdnhub_updates';

$cache = dle_cache($cache_hash);

if ($cache) {

	echo $cache;

	return false;

} else {

	$template = file_get_contents($template);

	if (!preg_match("#\[updates\].*?\[/updates\]#si", $template) || !preg_match("#\[update\].*?\[/update\]#si", $template))
		return false;

	// ORDER BY u.id DESC, u.season DESC, u.episode ASC

	$result = $db->query("SELECT
		u.post_id, u.token, u.update_date, u.translation_id, u.quality, u.season, u.episode,
		p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags,
		e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason
	FROM " . PREFIX . "_cdnhub_update_serials AS u
		LEFT JOIN " . PREFIX . "_post p ON (p.id = u.post_id)
		LEFT JOIN " . PREFIX . "_post_extras e ON (e.news_id = u.post_id)
	WHERE (u.update_date > DATE_SUB(now(), INTERVAL {$days} DAY)) AND p.approve = 1
	ORDER BY u.update_date DESC, u.season DESC, u.episode ASC");

	$updates = array();

	while ($row = $db->get_row($result))
		$updates[] = $row;

	$db->free();

	$day_data = array();

	foreach ($updates as $key => $update) {
		$day = date('d-m-Y', strtotime($update['update_date']));
		$day_data[$day][] = $update;
	}

	$new_data = array();

	foreach ($day_data as $date => $day) {
		foreach ($day as $row) {
			$update_post_id = intval($row['post_id']);
			$update_token = $row['token'];
			$update_season = intval($row['season']);

			$key = "{$update_post_id}_{$update_token}_{$update_season}";

			if (!$new_data[$date][$key])
				$new_data[$date][$key] = $row;

			if (!$new_data[$date][$key]['episodes'])
				$new_data[$date][$key]['episodes'] = array();

			$update_episode = intval($row['episode']);

			$new_data[$date][$key]['episodes'][] = $update_episode;
		}
	}

	$xfields = xfieldsload();

	preg_match("#\[updates\](.*?)\[/updates\]#si", $template, $output);
	$_template = $output[1];

	$data = $_template;

	preg_match("#\[day\](.*?)\[/day\]#si", $template, $output);
	$_day_template = $output[1];

	preg_match("#\[update\](.*?)\[/update\]#si", $template, $output);
	$_update_template = $output[1];

	$_days = '';

	$today = false;
	$yesterday = false;

	$_day_num = 1;

	foreach ($new_data as $date => $day) {
		$_days .= $_day_template;

		if (date('d-m-Y', time()) == $date)
			$today = true;

		if (date('d-m-Y', strtotime(date('d-m-Y') . '-1 days')) == $date)
			$yesterday = true;

		if ($today || $yesterday) {
			$_days = str_replace('[date-prefix]', '', $_days);
			$_days = str_replace('[/date-prefix]', '', $_days);

			if ($today)
				$_days = str_replace('{date-prefix}', 'Сегодня', $_days);
			else
				$_days = str_replace('{date-prefix}', 'Вчера', $_days);
		} else {
			$_days = preg_replace("#\[date-prefix\].*?\[/date-prefix\]#si", '', $_days);
		}

		$_date = date('j', strtotime($date)) . ' ' . $month[date('n', strtotime($date))] . ' ' . date('Y', strtotime($date));

		$_days = str_replace('{date}', $_date, $_days);

		$_days = str_replace('{day-num}', $_day_num, $_days);
		$_days = preg_replace("#\[day-{$_day_num}\](.*?)\[/day-{$_day_num}\]#si", "$1", $_days);
		$_days = preg_replace("#\[day-[0-9]+\].*?\[/day-[0-9]+\]#si", '', $_days);
		$_days = preg_replace("#\[not-day-{$_day_num}\].*?\[/not-day-{$_day_num}\]#si", '', $_days);
		$_days = preg_replace("#\[not-day-[0-9]+\](.*?)\[/not-day-[0-9]+\]#si", "$1", $_days);

		$_updates = '';

		$i = 1;

		foreach ($day as $row) {
			if ($limit && $i > $limit)
				break;

			$_updates .= $_update_template;

			// Translation

			if ($row['translation_id']) {
				$key = array_search($row['translation_id'], array_column($cdnhub->config['translations'], 'id'));
				$_updates = str_replace('{translation}', $cdnhub->config['translations'][$key]['title'], $_updates);
				$_updates = preg_replace("#\[translation\](.*?)\[/translation\]#si", "$1", $_updates);
			} else
				$_updates = preg_replace("#\[translation\].*?\[/translation\]#si", '', $_updates);

			// Quality

			if ($row['quality']) {
				$_updates = str_replace('{quality}', $row['quality'], $_updates);
				$_updates = preg_replace("#\[quality\](.*?)\[/quality\]#si", "$1", $_updates);
			} else
				$_updates = preg_replace("#\[quality\].*?\[/quality\]#si", '', $_updates);

			// Season

			if ($row['season']) {
				$_updates = str_replace('{season}', $row['season'], $_updates);
				$_updates = preg_replace("#\[season\](.*?)\[/season\]#si", "$1", $_updates);
			} else
				$_updates = preg_replace("#\[season\].*?\[/season\]#si", '', $_updates);

			// Episodes

			if ($row['episodes']) {
				sort($row['episodes']);

				if (count($row['episodes']) < 3)
					$episodes = implode(',', $row['episodes']);
				else
					$episodes = current($row['episodes']) . '-' . end($row['episodes']);

				$_updates = str_replace('{episodes}', $episodes, $_updates);
				$_updates = preg_replace("#\[episodes\](.*?)\[/episodes\]#si", "$1", $_updates);
			} else
				$_updates = preg_replace("#\[episodes\].*?\[/episodes\]#si", '', $_updates);

			// News Data -> Begin

			$row['date'] = $row['updated'];

			if (!$row['category']) {
				$my_cat = '---';
				$my_cat_link = '---';
			} else {
				$my_cat = array();
				$my_cat_link = array();
				$cat_list = explode(',', $row['category']);

				if ($config['category_separator'] != ',')
					$config['category_separator'] = ' ' . $config['category_separator'];
			 
				if (count($cat_list) == 1) {
					$my_cat[] = $cat_info[$cat_list[0]]['name'];
					
					$my_cat_link = get_categories($cat_list[0], $config['category_separator']);
				} else {
					foreach ($cat_list as $element) {
						if ($element) {
							$my_cat[] = $cat_info[$element]['name'];

							if ($config['allow_alt_url'])
								$my_cat_link[] = "<a href=\"{$config['http_home_url']}" . get_url($element) . "/\">{$cat_info[$element]['name']}</a>";
							else
								$my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
						}
					}
					
					$my_cat_link = implode("{$config['category_separator']} ", $my_cat_link);
				}
				
				$my_cat = implode("{$config['category_separator']} ", $my_cat);
			}

			$row['category'] = intval($row['category']);

			if ($config['allow_alt_url']) {
				if ($config['seo_type'] == 1 || $config['seo_type'] == 2) {
					if ($row['category'] && $config['seo_type'] == 2)
						$full_link = $config['http_home_url'] . get_url($row['category']) . "/{$row['id']}-{$row['alt_name']}.html";
					else
						$full_link = "{$config['http_home_url']}{$row['id']}-{$row['alt_name']}.html";
				} else
					$full_link = $config['http_home_url'] . date('Y/m/d/', $row['date']) . "{$row['alt_name']}.html";
			} else
				$full_link = $config['http_home_url'] . "index.php?newsid={$row['id']}";

			if (date('Ymd', $row['date']) == date('Ymd', $_TIME))
				$_updates = str_replace('{date}', $lang['time_heute'] . langdate(', H:i', $row['date']), $_updates);
			elseif (date('Ymd', $row['date']) == date('Ymd', ($_TIME - 86400)))
				$_updates = str_replace('{date}', $lang['time_gestern'] . langdate(', H:i', $row['date']), $_updates);
			else
				$_updates = str_replace('{date}', langdate($config['timestamp_active'], $row['date']), $_updates);

			$news_date = $row['date'];
			$_updates = preg_replace_callback("#\{date=(.+?)\}#i", 'formdate', $_updates);

			$_updates = str_replace('{category}', $my_cat, $_updates);
			$_updates = str_replace('{link-category}', $my_cat_link, $_updates);

			$row['title'] = stripslashes($row['title']);

			$row['title'] = str_replace('{', '&#123;', $row['title']);

			$_updates = str_replace('{title}', $row['title'], $_updates);

			if (preg_match("#\\{title limit=['\"](.+?)['\"]\\}#i", $_updates, $matches)) {
				$count = intval($matches[1]);

				$row['title'] = strip_tags($row['title']);

				if ($count && dle_strlen($row['title'], $config['charset']) > $count) {
					$row['title'] = dle_substr($row['title'], 0, $count, $config['charset']);

					if (($temp_dmax = dle_strrpos($row['title'], ' ', $config['charset'])))
						$row['title'] = dle_substr($row['title'], 0, $temp_dmax, $config['charset']) . ' ...';
				}

				$_updates = str_replace($matches[0], $row['title'], $_updates);
			}


			$_updates = str_replace('{full-link}', $full_link, $_updates);

			$row['short_story'] = stripslashes($row['short_story'] . $row['xfields']);
			$row['xfields'] = stripslashes($row['xfields']);

			if (stripos($row['short_story'], '[hide') !== false) {
				$row['short_story'] = preg_replace_callback(
					"#\[hide(.*?)\](.+?)\[/hide\]#is",
					function ($matches) use ($member_id, $user_group, $lang) {
						$matches[1] = str_replace(array('=', ' '), '', $matches[1]);
						$matches[2] = $matches[2];

						if ($matches[1]) {
							$groups = explode(',', $matches[1]);

							if (in_array($member_id['user_group'], $groups) || $member_id['user_group'] == '1')
								return $matches[2];
							else
								return '<div class="quote">' . $lang['news_regus'] . '</div>';
						} else {
							if ($user_group[$member_id['user_group']]['allow_hide'])
								return $matches[2];
							else
								return '<div class="quote">' . $lang['news_regus'] . '</div>';
						}
					},
					$row['short_story']
				);
			}

			if (stripos($_updates, '{image-') !== false) {
				$images = array();

				preg_match_all("#(img|src)=(\"|')[^\"'>]+#i", $row['short_story'], $media);
				$_data = preg_replace("#(img|src)(\"|'|=\"|=')(.*)#i", "$3", $media[0]);

				foreach ($_data as $url) {
					$info = pathinfo($url);

					if (isset($info['extension'])) {
						if ($info['filename'] == 'spoiler-plus' || $info['filename'] == 'spoiler-minus' || strpos($info['dirname'], 'engine/data/emoticons') !== false)
							continue;

						$info['extension'] = strtolower($info['extension']);

						if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png'))
							array_push($images, $url);
					}
				}

				if (count($images)) {
					$i = 0;

					foreach ($images as $url) {
						$i++;

						$_updates = str_replace("{image-{$i}}", $url, $_updates);
						$_updates = str_replace("[image-{$i}]", '', $_updates);
						$_updates = str_replace("[/image-{$i}]", '', $_updates);
					}
				}

				$_updates = preg_replace("#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", '', $_updates);
				$_updates = preg_replace("#\\{image-(.+?)\\}#i", '{THEME}/dleimages/no_image.jpg', $_updates);
			}

			$_updates = str_replace('{text}', $row['short_story'], $_updates);

			if (preg_match("#\\{text limit=['\"](.+?)['\"]\\}#i", $_updates, $matches)) {
				$count = intval($matches[1]);

				$row['short_story'] = str_replace('</p><p>', ' ', $row['short_story']);
				$row['short_story'] = strip_tags($row['short_story'], '<br>');
				$row['short_story'] = trim(str_replace('<br>', ' ', str_replace('<br />', ' ', str_replace("\n", ' ', str_replace("\r", '', $row['short_story'])))));

				if ($count && dle_strlen($row['short_story'], $config['charset']) > $count) {
					$row['short_story'] = dle_substr($row['short_story'], 0, $count, $config['charset']);
						
					if (($temp_dmax = dle_strrpos($row['short_story'], ' ', $config['charset'])))
						$row['short_story'] = dle_substr($row['short_story'], 0, $temp_dmax, $config['charset']);
				}

				$_updates = str_replace($matches[0], $row['short_story'], $_updates);
			}

			// xfields

			if (count($xfields)) {
				$xfieldsdata = xfieldsdataload($row['xfields']);

				foreach ($xfields as $value) {
					$preg_safe_name = preg_quote($value[0], "'");
					
					if ($value[20]) {
						$value[20] = explode(',', $value[20]);

						if ($value[20][0] && !in_array($member_id['user_group'], $value[20]))
							$xfieldsdata[$value[0]] = "";
					}
					
					if ($value[3] == 'yesorno') {
					    if (intval($xfieldsdata[$value[0]])) {
							$xfgiven = true;
							$xfieldsdata[$value[0]] = $lang['xfield_xyes'];
						} else {
							$xfgiven = false;
							$xfieldsdata[$value[0]] = $lang['xfield_xno'];
						}
					} else {
						if ($xfieldsdata[$value[0]] == '')
							$xfgiven = false;
						else
							$xfgiven = true;
					}
					
					if (!$xfgiven) {
						$_updates = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", '', $_updates);
						$_updates = str_replace("[xfnotgiven_{$value[0]}]", '', $_updates);
						$_updates = str_replace("[/xfnotgiven_{$value[0]}]", '', $_updates);
					} else {
						$_updates = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", '', $_updates);
						$_updates = str_replace( "[xfgiven_{$value[0]}]", '', $_updates);
						$_updates = str_replace( "[/xfgiven_{$value[0]}]", '', $_updates);
					}

					if (strpos($_updates, '[ifxfvalue') !== false)
						$_updates = preg_replace_callback("#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", 'check_xfvalue', $_updates);
					
					if ($value[6] AND !empty($xfieldsdata[$value[0]])) {
						$temp_array = explode(',', $xfieldsdata[$value[0]]);
						$value3 = array();

						foreach ($temp_array as $value2) {
							$value2 = trim($value2);
							$value2 = str_replace('&#039;', "'", $value2);

							if ($config['allow_alt_url'])
								$value3[] = "<a href=\"{$config['http_home_url']}xfsearch/{$value[0]}/" . urlencode($value2) . "/\">{$value2}</a>";
							else
								$value3[] = "<a href=\"{$PHP_SELF}?do=xfsearch&amp;xfname={$value[0]}&amp;xf=" . urlencode($value2) . "\">{$value2}</a>";
						}

						$xfieldsdata[$value[0]] = implode(', ', $value3);

						unset($temp_array);
						unset($value2);
						unset($value3);
					}
					
					if ($value[3] == 'image' AND $xfieldsdata[$value[0]]) {
						$path_parts = @pathinfo($xfieldsdata[$value[0]]);

						if ($value[12] && file_exists(ROOT_DIR . "/uploads/posts/{$path_parts['dirname']}/thumbs/{$path_parts['basename']}")) {
							$thumb_url = "{$config['http_home_url']}uploads/posts/{$path_parts['dirname']}/thumbs/{$path_parts['basename']}";
							$img_url = "{$config['http_home_url']}uploads/posts/{$path_parts['dirname']}/{$path_parts['basename']}";
						} else {
							$img_url = "{$config['http_home_url']}uploads/posts/{$path_parts['dirname']}/{$path_parts['basename']}";
							$thumb_url = '';
						}
						
						if ($thumb_url) {
							$xfieldsdata[$value[0]] = "<a href=\"{$img_url}\" class=\"highslide\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$thumb_url}\" alt=\"\" /></a>";
						} else
							$xfieldsdata[$value[0]] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$img_url}\" alt=\"\" />";
					}
					
					if ($value[3] == 'image') {
						if ($xfieldsdata[$value[0]]) {
							$_updates = str_replace("[xfvalue_thumb_url_{$value[0]}]", $thumb_url, $_updates);
							$_updates = str_replace("[xfvalue_image_url_{$value[0]}]", $img_url, $_updates);
						} else {
							$_updates = str_replace("[xfvalue_thumb_url_{$value[0]}]", '', $_updates);
							$_updates = str_replace("[xfvalue_image_url_{$value[0]}]", '', $_updates);
						}
					}
					
					if ($value[3] == 'imagegalery' && $xfieldsdata[$value[0]] && stripos($_updates, "[xfvalue_{$value[0]}]") !== false) {
						$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);
						$gallery_image = array();
						
						foreach ($fieldvalue_arr as $temp_value) {
							$temp_value = trim($temp_value);
					
							if($temp_value == '')
								continue;

							$path_parts = @pathinfo($temp_value);
									
							if ($value[12] && file_exists(ROOT_DIR . "/uploads/posts/{$path_parts['dirname']}/thumbs/{$path_parts['basename']}")) {
								$thumb_url = "{$config['http_home_url']}uploads/posts/{$path_parts['dirname']}/thumbs/{$path_parts['basename']}";
								$img_url = "{$config['http_home_url']}uploads/posts/{$path_parts['dirname']}/{$path_parts['basename']}";
							} else {
								$img_url = "{$config['http_home_url']}uploads/posts/{$path_parts['dirname']}/{$path_parts['basename']}";
								$thumb_url = '';
							}
							
							if ($thumb_url)
								$gallery_image[] = "<li><a href=\"$img_url\" onclick=\"return hs.expand(this, { slideshowGroup: 'xf_{$row['id']}_{$value[0]}' })\" target=\"_blank\"><img src=\"{$thumb_url}\" alt=\"\" /></a></li>";
							else
								$gallery_image[] = "<li><img src=\"{$img_url}\" alt=\"\" /></li>";
						
						}
						
						$xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">" . implode($gallery_image) . '</ul>';
						$uniq_id = "xf_{$row['id']}_{$value[0]}";
						$onload_scripts[$uniq_id] = "hs.addSlideshow({slideshowGroup: '{$uniq_id}', interval: 4000, repeat: false, useControls: true, fixedControls: 'fit', overlayOptions: { opacity: .75, position: 'bottom center', hideOnMouseOut: true } });";
					}

					$_updates = str_replace("[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $_updates);

					if (preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $_updates, $matches)) {
						$count = intval($matches[1]);

						$xfieldsdata[$value[0]] = str_replace('</p><p>', ' ', $xfieldsdata[$value[0]]);
						$xfieldsdata[$value[0]] = strip_tags($xfieldsdata[$value[0]], '<br>');
						$xfieldsdata[$value[0]] = trim(str_replace('<br>', ' ', str_replace('<br />', ' ', str_replace("\n", ' ', str_replace("\r", '', $xfieldsdata[$value[0]])))));

						if ($count && dle_strlen($xfieldsdata[$value[0]], $config['charset']) > $count) {
							$xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $count, $config['charset']);
								
							if (($temp_dmax = dle_strrpos($xfieldsdata[$value[0]], ' ', $config['charset'])))
								$xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset']);
						}

						$_updates = str_replace($matches[0], $xfieldsdata[$value[0]], $_updates);
					}
				}
			}

			// News Data -> End

			$i++;
		}

		$_days = str_replace('{updates}', $_updates, $_days);

		$today = false;
		$yesterday = false;

		$_day_num++;
	}

	$data = preg_replace("#\[day\].*?\[/day\]#si", $_days, $data);

	create_cache($cache_hash, $data);

	echo $data;

}

$config['allow_cache'] = $allow_cache;
$config['clear_cache'] = $clear_cache;
$config['cache_type'] = $cache_type;