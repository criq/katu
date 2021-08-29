<?php

namespace Katu\Utils;

class FileStorage {

	static function set($name, $value) {
		return static::setValue($name, serialize($value));
	}

	static function get($name) {
		return unserialize(static::getValue($name));
	}

	static function setValue($name, $value) {
		$path = static::getPath($name);
		@mkdir(dirname($path), 0777, true);
		file_put_contents($path, $value);

		return $path;
	}

	static function getValue($name) {
		if (file_exists(static::getPath($name))) {
			return file_get_contents(static::getPath($name));
		}

		return null;
	}

	static function getPath($name) {
		return FILE_PATH . FileSystem::getPathForName($name);
	}

	static function getSafeName($name) {
		return preg_replace('#_+#', '_', preg_replace('#[^a-zA-Z0-9\-\_\.]#', '_', trim(\URLify::downcode($name))));
	}

}
