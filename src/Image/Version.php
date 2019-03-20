<?php

namespace Katu\Image;

class Version {

	protected $name = null;
	protected $filters = [];
	protected $quality = 100;
	protected $extension;

	public function __construct($name = null) {
		$this->name = (string)$name;
	}

	static function createFromConfig($name) {
		$config = \Katu\Config::get('image', 'versions', $name);

		$version = new static($name);

		if (isset($config['filters'])) {
			foreach ((array)$config['filters'] as $filterConfig) {
				$filter = \Katu\Image\Filter::createByCode($filterConfig['filter']);
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

	public function getName() {
		return $this->name ?: $this->getHash();
	}

	public function getArray() {
		$array = [];

		foreach ($this->filters as $filter) {
			$array[] = $filter->getArray();
		}

		$array['quality'] = $this->quality;
		$array['extension'] = $this->extension;

		return $array;
	}

	public function getHash() {
		return sha1(\Katu\Utils\JSON::encodeStandard($this->getArray()));
	}

	public function setQuality($quality) {
		$this->quality = $quality;

		return $this;
	}

	public function getQuality() {
		return $this->quality;
	}

	public function setExtension($extension) {
		$this->extension = $extension;

		return $this;
	}

	public function getExtension() {
		return $this->extension;
	}

	public function getDir() {
		$dir = new \Katu\Utils\File(BASE_DIR, \Katu\Config::get('app', 'tmp', 'publicDir'), 'image', 'versions', $this->getName());
		if (!$dir->isWritable()) {
			try {
				$dir->makeDir();
			} catch (\Exception $e) {
				throw new \Katu\Exceptions\ErrorException("Can't create image version folder at $dir.");
			}
		}

		return $dir;
	}

	public function addFilter($filter) {
		$this->filters[] = $filter;

		return $this;
	}

	public function getFilters() {
		return $this->filters;
	}

}
