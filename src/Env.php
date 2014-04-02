<?php

namespace Jabli;

class Env {

	const ENV_DEV  = 0;
	const ENV_PROD = 1;

	static function getHash() {
		$hash = self::getWholeHash();

		return substr($hash, 0, 4) . substr($hash, -4, 4);
	}

	static function getPlatform() {
		$path = BASE_DIR . '/app/.platform';
		if (!file_exists($path)) {
			throw new Exception("Missing platform file.");
		}

		if (!is_readable($path)) {
			throw new Exception("Unable to read platform.");
		}

		return trim(file_get_contents($path));
	}

	static function getWholeHash() {
		return sha1(Utils\JSON::encodeStandard(self::getEnvProperties()));
	}

	static function getEnvProperties() {
		return array(
			'host' => $_SERVER['SERVER_NAME'],
			'dir'  => BASE_DIR,
		);
	}

}
