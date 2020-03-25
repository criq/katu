<?php

namespace Katu\Tools\Services\Composer;

class Composer
{
	public static function getJSON()
	{
		$path = \Katu\Files\File::joinPaths(\Katu\App::getBaseDir(), 'composer.json');
		if (!file_exists($path)) {
			throw new \Exception("Missing composer.json file.");
		}

		if (!is_readable($path)) {
			throw new \Exception("Unable to read composer.json file.");
		}

		return \Katu\Files\Formats\JSON::decodeAsArray(file_get_contents($path));
	}

	public static function getDir()
	{
		$json = self::getJSON();
		if (isset($json['config']['vendor-dir'])) {
			return new \Katu\Files\File(\Katu\Files\File::joinPaths(\Katu\App::getBaseDir(), $json['config']['vendor-dir']));
		}

		return new \Katu\Files\File(\Katu\Files\File::joinPaths(\Katu\App::getBaseDir(), 'vendor'));
	}
}
