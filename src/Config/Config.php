<?php

namespace Katu\Config;

use Katu\Files\File;
use Katu\Files\FileCollection;
use Katu\Types\TArray;
use Katu\Types\TIdentifier;

class Config
{
	protected $title;
	protected $config;

	public function __construct(string $title, array $config)
	{
		$this->setTitle($title);
		$this->setConfig($config);
	}

	public static function createFromFile(\Katu\Files\File $file): ?Config
	{
		switch ($file->getExtension()) {
			case "php":
				return new static($file->getFilename(), (array)include $file);
				break;
			case "yml":
			case "yaml":
				return new static($file->getFilename(), (array)\Katu\Files\Formats\YAML::decode(preg_replace_callback("/\\$\{(?<title>[A-Z_][A-Z0-9_]*)\}/m", function ($match) {
					return $_ENV[$match["title"]] ?? null;
				}, $file->get())));
				break;
		}

		return null;
	}

	public function setTitle(string $title): Config
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setConfig(array $config): Config
	{
		$this->config = $config;

		return $this;
	}

	public function getConfig(): array
	{
		return $this->config;
	}

	public static function getFiles(): FileCollection
	{
		return new FileCollection(array_values(array_filter(array_map(function (string $filename) {
			return new File(\App\App::getConfigDir(), $filename);
		}, scandir(\App\App::getConfigDir())), function (\Katu\Files\File $file) {
			return Config::getIsSupportedFile($file);
		})));
	}

	public static function getIsSupportedFile(\Katu\Files\File $file): bool
	{
		return in_array($file->getExtension(), [
			"php",
			"yaml",
			"yml",
		]);
	}

	public static function getArray(): array
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__), function () {
			return ConfigCollection::createDefault()->getArray();
		});
	}

	public static function getTArray(): TArray
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__), function () {
			return new TArray(static::getArray());
		});
	}

	public static function get()
	{
		$args = func_get_args();

		try {
			return call_user_func_array([static::getTArray(), "getValueByArgs"], $args);
		} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
			$path = implode(".", $args);

			throw new \Katu\Exceptions\MissingConfigException("Missing config for {$path}.");
		}
	}
}
