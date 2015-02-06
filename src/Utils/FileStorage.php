<?php

namespace Katu\Utils;

class FileStorage {

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
		return FILE_PATH . FileSystem::getPathForName($name);
	}

}
