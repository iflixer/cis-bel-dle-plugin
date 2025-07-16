<?php

class CDNHubNewsXfields
{

	protected $xf;

	public function __construct()
	{

		$this->xf = xfieldsload();

	}

	// Xfsearch

	public function xfsearch($post_id, $data)
	{

		global $db;

		foreach ($data as $field => $values) {
			if ($values && false !== $xf_key = array_search($field, array_column($this->xf, 0))) {
				if ($this->xf[$xf_key][6]) {
					$tagname = $db->safesql($field);

					$db->query("DELETE FROM " . PREFIX . "_xfsearch WHERE `news_id` = '{$post_id}' AND `tagname` = '{$tagname}'");

					$values = explode(',', $values);

					$xfsearch = array();
					
					foreach ($values as  $key => $value) {
						$tagvalue = $db->safesql($value);

						$xfsearch[] = "('{$post_id}', '{$tagname}', '{$tagvalue}')";
					}

					$db->query("INSERT INTO " . PREFIX . "_xfsearch (`news_id`, `tagname`, `tagvalue`) VALUES " . implode(', ', $xfsearch));
				}
			}
		}

	}

	// String To Array

	public function toArray($data)
	{

		if (!is_string($data))
			return false;

		if (!$data)
			return array();

		$result = array();

		$rows = explode('||', $data);

		foreach ($rows as $row) {
			
			list($key, $value) = explode('|', $row);

			$key = str_replace("&#124;", "|", $key);
			$key = str_replace("__NEWL__", "\r\n", $key);

			$value = str_replace("&#124;", "|", $value);
			$value = str_replace("__NEWL__", "\r\n", $value);

			$result[$key] = trim($value);

		}

		return $result;

	}

	// Array To String

	public function toString($data)
	{

		if (!is_array($data))
			return false;

		if (!$data)
			return '';

		$result = array();

		foreach ($data as $key => $value) {
			
			$key = str_replace("|", "&#124;", $key);
			$key = str_replace("\r\n", "__NEWL__", $key);

			$value = str_replace("|", "&#124;", $value);
			$value = str_replace("\r\n", "__NEWL__", $value);

			$result[] = trim("{$key}|{$value}");

		}

		$result = implode('||', $result);

		return $result;
		
	}

}