<?php

namespace Katu\Utils;

class Tmp {

	static function set($name, $value) {
		$path = static::getPath($name);
		file_put_contents($path, $value);

		return $path;
	}

	static function getPath($name) {
		return TMP_PATH . static::getFileName($name);
	}

	static function getFileName($name) {
		return implode('__', (array) $name);
	}

}
