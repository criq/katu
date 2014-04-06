<?php

namespace Jabli\Utils;

class INI {

	static function parse($path) {
		$src = @parse_ini_file($path, TRUE);
		if ($src === FALSE) {
			throw new Exception("Invalid INI file.");
		}

		$ini = array();
		foreach ($src as $section => $values) {
			$pointer =& $ini;
			$level = 0;
			$section_names = explode('.', $section);
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
