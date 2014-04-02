<?php

namespace Jabli;

class Keychain {

	static function get() {
		$array = new \Jabli\Types\FWArray(self::getAll());

		return call_user_func_array(array($array, 'getValueByArgs'), func_get_args());
	}

	static function getAll() {
		if (!defined('BASE_DIR')) {
			throw new Exception("Undefined BASE_DIR.");
		}

		$path = BASE_DIR . '/app/.keychain';
		if (!file_exists($path)) {
			throw new Exception("Missing keychain file.");
		}

		if (!is_readable($path)) {
			throw new Exception("Unable to read keychain file.");
		}

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
