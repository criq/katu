<?php

namespace Katu\Utils;

class Tmp {

	static function set($name, $value) {
		$path = static::getPath($name);
		file_put_contents($path, $value);

		return $path;
	}

	static function get($name) {
		if (file_exists(static::getPath($name))) {
			return file_get_contents(static::getPath($name));
		}

		return null;
	}

	static function getPath($name) {
		return TMP_PATH . static::getFileName($name);
	}

	static function getFileName($name) {
		return implode('__', (array) $name);
	}

	static function debug($var) {
		return static::set(implode('.', ['debug', time(), Random::getFileName(8)]), serialize($var));
	}

}
