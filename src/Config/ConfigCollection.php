<?php

namespace Katu\Config;

class ConfigCollection extends \ArrayObject
{
	public static function createDefault(): ConfigCollection
	{
		return new static(array_map(function (\Katu\Files\File $file) {
			return Config::createFromFile($file);
		}, Config::getFiles()->getArrayCopy()));
	}

	public function filterByTitle(string $title): ConfigCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Config $config) use ($title) {
			return $config->getTitle() == $title;
		})));
	}

	public function getFirst(): ?Config
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getArray(): array
	{
		return array_merge_recursive(...array_map(function (Config $config) {
			return [
				$config->getTitle() => $config->getConfig(),
			];
		}, $this->getArrayCopy()));
	}
}
