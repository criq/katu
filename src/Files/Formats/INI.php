<?php

namespace Katu\Files\Formats;

class INI
{
	public static function parse($path)
	{
		$src = @parse_ini_file($path, true);
		if ($src === false) {
			throw new \Exception("Invalid INI file.");
		}

		$ini = [];
		foreach ($src as $section => $values) {
			$pointer =& $ini;
			$level = 0;
			$section_names = explode(".", $section);
			foreach ($section_names as $section_name) {
				if (++$level == count($section_names)) {
					$pointer[$section_name] = $values;
				}
				$pointer =& $pointer[$section_name];
			}
		}

		return $ini;
	}
}
