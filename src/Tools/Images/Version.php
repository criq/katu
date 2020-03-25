<?php

namespace Katu\Tools\Images;

class Version
{
	const SEPARATOR = ".";

	protected $extension;
	protected $filters = [];
	protected $name = null;
	protected $quality = 100;

	public function __construct($name = null)
	{
		$this->name = (string)$name;
	}

	public static function createFromConfig()
	{
		$args = func_get_args();

		try {
			if (isset($args[0]) && is_string($args[0])) {
				$name = $args[0];

				$config = \Katu\Config\Config::get('image', 'versions', $name);

				$version = new static($name);

				if (isset($config['filters'])) {
					foreach ((array)$config['filters'] as $filterConfig) {
						$filter = \Katu\Tools\Images\Filter::createByCode($filterConfig['filter']);
						unset($filterConfig['filter']);
						$filter->setParams($filterConfig);
						$version->addFilter($filter);
					}
				}

				if (isset($config['quality'])) {
					$version->setQuality($config['quality']);
				}

				if (isset($config['extension'])) {
					$version->setExtension($config['extension']);
				}

				return $version;
			}

			throw new \Katu\Exceptions\MissingConfigException;
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$versionClass = '\\App\\Extensions\\Image\\Version';
			if (class_exists($versionClass)) {
				$version = call_user_func_array([$versionClass, 'createFromConfig'], func_get_args());

				return $version;
			}
		}
	}

	public function getName()
	{
		return $this->name ?: $this->getHash();
	}

	public function getArray()
	{
		$array = [];

		foreach ($this->filters as $filter) {
			$array[] = $filter->getArray();
		}

		$array['quality'] = $this->quality;
		$array['extension'] = $this->extension;

		return $array;
	}

	public function getHash()
	{
		return sha1(\Katu\Files\Formats\JSON::encodeStandard($this->getArray()));
	}

	public function setQuality($quality)
	{
		$this->quality = $quality;

		return $this;
	}

	public function getQuality()
	{
		return $this->quality;
	}

	public function setExtension($extension)
	{
		$this->extension = $extension;

		return $this;
	}

	public function getExtension()
	{
		return $this->extension;
	}

	public function getDir()
	{
		$dir = new \Katu\Files\File(\Katu\App::getBaseDir(), \Katu\Config\Config::get('app', 'tmp', 'publicDir'), 'image', 'versions', $this->getName());
		if (!$dir->isWritable()) {
			try {
				$dir->makeDir();
			} catch (\Exception $e) {
				throw new \Katu\Exceptions\ErrorException("Can't create image version folder at $dir.");
			}
		}

		return $dir;
	}

	public function addFilter(Filter $filter)
	{
		$this->filters[] = $filter;

		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}
}
