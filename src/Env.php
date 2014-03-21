<?php

namespace Jabli;

class Env {

	static function getHash() {
		$hash = self::getWholeHash();

		return substr($hash, 0, 4) . substr($hash, -4, 4);
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
