<?php

namespace Katu\Config;

class Env {

	const ENV_DEV  = 0;
	const ENV_PROD = 1;

	static function getHash() {
		$hash = self::getWholeHash();

		return substr($hash, 0, 4) . substr($hash, -4, 4);
	}

	static function getPlatform() {
		$paths = array(
			BASE_DIR . '/.platform',
			BASE_DIR . '/app/.platform',
		);

		foreach ($paths as $path) {
			if (file_exists($path) && is_readable($path)) {
				return trim(file_get_contents($path));
			}
		}

		return false;
	}

	static function getWholeHash() {
		return sha1(\Katu\Files\Formats\JSON::encodeStandard(self::getEnvProperties()));
	}

	static function getEnvProperties() {
		return array(
			'host' => $_SERVER['SERVER_NAME'],
			'dir'  => BASE_DIR,
		);
	}

}
