<?php

namespace Katu\Tools\Services\Composer;

class Composer {

	static function getJSON() {
		$path = \Katu\Tools\Files\File::joinPaths(BASE_DIR, 'composer.json');
		if (!file_exists($path)) {
			throw new \Exception("Missing composer.json file.");
		}

		if (!is_readable($path)) {
			throw new \Exception("Unable to read composer.json file.");
		}

		return \Katu\Tools\Files\Formats\JSON::decodeAsArray(file_get_contents($path));
	}

	static function getDir() {
		$json = self::getJSON();
		if (isset($json['config']['vendor-dir'])) {
			return realpath(\Katu\Tools\Files\File::joinPaths(BASE_DIR, $json['config']['vendor-dir']));
		}

		return realpath(\Katu\Tools\Files\File::joinPaths(BASE_DIR, 'vendor'));
	}

}
