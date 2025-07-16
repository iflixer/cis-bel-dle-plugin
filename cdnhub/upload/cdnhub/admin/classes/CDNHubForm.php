<?php

class CDNHubForm
{

	// Group

	public static function group($id, $text, $content, $description = false)
	{

		return "<div class=\"col-md-6 mb-3\">
			". ($text ? "<label for=\"{$id}\">{$text}</label>" : '') . "
			{$content}
			" . ($description ? "<div class=\"text-muted mt-1\">{$description}</div>" : '') . "
		</div>";

	}

	// text

	public static function text($id, $name, $value, $placeholder = false)
	{

		return "<input type=\"text\" class=\"form-control\" id=\"{$id}\" name=\"{$name}\" value=\"" . cdnhub_encode($value) . "\"" . ($placeholder ? " placeholder=\"{$placeholder}\"" : '') . ">";

	}

	// Textarea

	public static function textarea($id, $name, $value)
	{

		return "<textarea type=\"text\" id=\"{$id}\" name=\"{$name}\" class=\"form-control\">" . cdnhub_encode($value) . "</textarea>";

	}

	// Switch

	public static function _switch($id, $name, $on = false)
	{

		return "<div class=\"vh-switch\">
			<div class=\"form-check form-switch\">
				<input type=\"checkbox\" class=\"form-check-input\" id=\"{$id}\" name=\"{$name}\"" . ($on ? ' checked' : '') . ">
				<label class=\"form-check-label\" for=\"{$id}\"></label>
			</div>
		</div>";

	}

	// Checkbox

	public static function checkbox($id, $name, $text, $checked, $disabled = false)
	{
		if ($disabled)
			$checked = false;
		
		return "<div class=\"form-check my-1 mr-sm-2\">
			<input type=\"checkbox\" name=\"{$name}\" value=\"1\" class=\"form-check-input\" id=\"{$id}\"" . ($disabled ? ' disabled' : '') . ($checked ? ' checked' : '') . ">
			<label class=\"form-check-label\" for=\"{$id}\">{$text}</label>
		</div>";

	}

	// Radio

	public static function radio($id, $name, $text, $key, $value)
	{

		return "<div class=\"form-check\">
      <label class=\"form-check-label\" for=\"{$id}\">
				<input type=\"radio\" id=\"{$id}\" name=\"{$name}\" value=\"{$key}\" class=\"form-check-input\"" . ($key == $value ? ' checked' : '') . ">
				{$text}
			</label>
    </div>";

	}

	// Select

	public static function select($id, $name, $data, $selected)
	{

		$result = "<select id=\"{$id}\" name=\"{$name}\" class=\"form-select\">";
			
		foreach ($data as $key => $value)
			$result .= "<option value=\"" . cdnhub_encode($key) . "\"" . ($key == $selected ? ' selected' : '') . ">" . cdnhub_encode($value) . "</option>";

		$result .= '</select>';

		return $result;

	}

	// Multiselect

	public static function multiselect($id, $name, $data, $selected)
	{

		$result = "<select id=\"{$id}\" name=\"{$name}[]\" class=\"form-control\" multiple>";
			
		if (is_array($data)) {
			foreach ($data as $key => $value)
				$result .= "<option value=\"" . cdnhub_encode($key) . "\"" . (in_array($key, $selected) ? ' selected' : '') . ">" . cdnhub_encode($value) . "</option>";
		} else
			$result .= $data;

		$result .= '</select>';

		return $result;

	}

}