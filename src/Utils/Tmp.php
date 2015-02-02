<?php

namespace Katu\Utils;

class Tmp {

	static function set($name, $value) {
		$path = static::getPath($name);
		@mkdir(dirname($path), 0777, true);
		file_put_contents($path, serialize($value));

		return $path;
	}

	static function get($name) {
		if (file_exists(static::getPath($name))) {
			return unserialize(file_get_contents(static::getPath($name)));
		}

		return null;
	}

	static function getPath($name) {
		return TMP_PATH . FS::getPathForName($name);
	}

	static function debug($var) {
		return static::set(implode('.', ['debug', time(), Random::getFileName(8)]), serialize($var));
	}

}
