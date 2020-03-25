<?php

namespace Katu\Config;

class Env
{
	const ENV_DEV  = 0;
	const ENV_PROD = 1;

	public static function getHash()
	{
		$hash = self::getWholeHash();

		return substr($hash, 0, 4) . substr($hash, -4, 4);
	}

	public static function getPlatform()
	{
		$files = [
			new \Katu\Files\File(\Katu\App::getBaseDir(), '.platform'),
			new \Katu\Files\File(\Katu\App::getBaseDir(), 'app', '.platform'),
		];

		foreach ($files as $file) {
			if ($file->exists() && $file->isReadable()) {
				return trim($file->get());
			}
		}

		return false;
	}

	public static function getWholeHash()
	{
		return sha1(\Katu\Files\Formats\JSON::encodeStandard(self::getEnvProperties()));
	}

	public static function getEnvProperties()
	{
		return array(
			'host' => $_SERVER['SERVER_NAME'],
			'dir'  => \Katu\App::getBaseDir(),
		);
	}
}
