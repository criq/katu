<?php

namespace Katu\Config;

use Katu\Types\TIdentifier;

class Config
{
	const FILENAME_REGEXP = "/^(?<name>[a-z0-9]+)(\.(?<platform>[a-z0-9]+))?\.(?<type>php|yaml)$/i";

	public static function get()
	{
		$args = func_get_args();

		try {
			return call_user_func_array([new \Katu\Types\TArray(static::getAll()), "getValueByArgs"], $args);
		} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
			$path = implode(".", $args);
			throw new \Katu\Exceptions\MissingConfigException("Missing config for $path.");
		}
	}

	public static function getWithDefault()
	{
		$args = func_get_args();
		$argConfig = array_slice($args, 0, -1);
		$argDefault = array_slice($args, -1, 1);

		try {
			return static::get(...$argConfig);
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return $argDefault[0] ?? null;
		}
	}

	public static function getAll()
	{
		return \Katu\Cache\Runtime::get(new TIdentifier("config"), function () {
			$cacheConfigFile = new \Katu\Files\File(\App\App::getBaseDir(), ".cacheconfig");
			if ($cacheConfigFile->exists()) {
				$cacheFile = new \Katu\Files\File(\App\App::getTemporaryDir(), "config", \Katu\Config\Env::getVersion());
				if ($cacheFile->exists()) {
					return unserialize($cacheFile->get());
				}
			}

			$config = [];

			foreach (static::getFiles() as $file) {
				if (preg_match(static::FILENAME_REGEXP, $file->getBasename(), $match)) {
					if (!$match["platform"] || $match["platform"] == Env::getPlatform()) {
						if ($match["type"] == "yaml") {
							$config[$match["name"]] = array_merge_recursive($config[$match["name"]] ?? [], (array)\Katu\Files\Formats\YAML::decode($file));
						} elseif ($match["type"] == "php") {
							$config[$match["name"]] = array_merge_recursive($config[$match["name"]] ?? [], (array)include $file);
						}
					}
				}
			}

			$config = array_merge_recursive($config, $_SERVER["CONFIG"] ?? []);

			$envConfigFile = new \Katu\Files\File(\App\App::getBaseDir(), ".config.yaml");
			if ($envConfigFile->exists()) {
				$envConfig = \Katu\Files\Formats\YAML::decode($envConfigFile);
				$config = array_merge_recursive($config, $envConfig ?? []);
			}

			if ($cacheConfigFile->exists()) {
				$cacheFile->set(serialize($config));
			}

			return $config;
		});
	}

	public static function getFiles()
	{
		$dir = new \Katu\Files\File(\App\App::getBaseDir(), "app", "Config");
		$files = [];

		foreach (scandir($dir) as $file) {
			if (preg_match(static::FILENAME_REGEXP, $file)) {
				$files[] = new \Katu\Files\File($dir, $file);
			}
		}

		return $files;
	}
}
