<?php

namespace Katu\Utils;

class Composer
{
	public static function getJSON()
	{
		$path = FileSystem::joinPaths(BASE_DIR, 'composer.json');
		if (!file_exists($path)) {
			throw new \Exception("Missing composer.json file.");
		}

		if (!is_readable($path)) {
			throw new \Exception("Unable to read composer.json file.");
		}

		return JSON::decodeAsArray(file_get_contents($path));
	}

	public static function getDir()
	{
		$json = self::getJSON();
		if (isset($json['config']['vendor-dir'])) {
			return realpath(FileSystem::joinPaths(BASE_DIR, $json['config']['vendor-dir']));
		}

		return realpath(FileSystem::joinPaths(BASE_DIR, 'vendor'));
	}

	public static function getInstalledJSON()
	{
		$file = new \Katu\Utils\File(static::getDir(), 'composer', 'installed.json');
		$res = \Katu\Utils\JSON::decodeAsArray($file->get());

		return $res['packages'] ?? $res;
	}

	public static function getPackageInfo($packageName)
	{
		$packages = array_values(array_filter(static::getInstalledJSON(), function ($i) use ($packageName) {
			return $i['name'] == $packageName;
		}));

		if (isset($packages[0])) {
			return $packages[0];
		}

		return null;
	}

	public static function getVersion($packageName)
	{
		$packageInfo = static::getPackageInfo($packageName);

		if (isset($packageInfo['version_normalized'])) {
			return $packageInfo['version_normalized'];
		}

		return null;
	}
}
