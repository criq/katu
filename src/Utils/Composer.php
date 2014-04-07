<?php

namespace Jabli\Utils;

class Composer {

	static function getJSON() {
		$path = FS::joinPaths(BASE_DIR, 'composer.json');
		if (!file_exists($path)) {
			throw new Exception("Missing composer.json file.");
		}

		if (!is_readable($path)) {
			throw new Exception("Unable to read composer.json file.");
		}

		return JSON::decodeAsArray(file_get_contents($path));
	}

	static function getDir() {
		$json = self::getJSON();
		if (isset($json['config']['vendor-dir'])) {
			return realpath(FS::joinPaths(BASE_DIR, $json['config']['vendor-dir']));
		}

		return realpath(FS::joinPaths(BASE_DIR, 'vendor'));
	}

}
