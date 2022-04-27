<?php

namespace Katu\Tools\Images;

class Version
{
	const SEPARATOR = ".";

	protected $extension;
	protected $filters;
	protected $name;
	protected $quality;

	public function __construct(?string $name = null, ?array $filters = [], ?string $extension = "jpg", ?int $quality = 100)
	{
		$this->setName($name);
		$this->setFilters($filters);
		$this->setExtension($extension);
		$this->setQuality($quality);
	}

	public static function createFromConfig(string $name): Version
	{
		$config = \Katu\Config\Config::get("image", "versions", $name);

		$version = new static($name);

		if (isset($config["filters"])) {
			foreach ((array)$config["filters"] as $filterConfig) {
				$filter = \Katu\Tools\Images\Filter::createByCode($filterConfig["filter"]);
				unset($filterConfig["filter"]);
				$filter->setParams($filterConfig);
				$version->addFilter($filter);
			}
		}

		if (isset($config["quality"])) {
			$version->setQuality($config["quality"]);
		}

		if (isset($config["extension"])) {
			$version->setExtension($config["extension"]);
		}

		return $version;
	}

	public function setName(?string $value): Version
	{
		$this->name = $value;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getHash(): string
	{
		return sha1(\Katu\Files\Formats\JSON::encodeStandard($this->getArray()));
	}

	public function setQuality(int $quality): Version
	{
		$this->quality = $quality;

		return $this;
	}

	public function getQuality(): int
	{
		return $this->quality;
	}

	public function setExtension(string $extension): Version
	{
		$this->extension = $extension;

		return $this;
	}

	public function getExtension(): ?string
	{
		return $this->extension;
	}

	public function getDir(): \Katu\Files\File
	{
		$dir = new \Katu\Files\File(\App\App::getPublicTemporaryDir(), "image", "versions", $this->getName(), $this->getHash());
		if (!$dir->isWritable()) {
			try {
				$dir->makeDir();
			} catch (\Throwable $e) {
				throw new \Katu\Exceptions\ErrorException("Can't create image version folder at {$dir}.");
			}
		}

		return $dir;
	}

	public function setFilters(?array $filters): Version
	{
		$this->filters = [];

		foreach ((array)$filters as $filter) {
			$this->addFilter($filter);
		}

		return $this;
	}

	public function addFilter(Filter $filter): Version
	{
		$this->filters[] = $filter;

		return $this;
	}

	public function getFilters(): array
	{
		return $this->filters;
	}

	public function getArray(): array
	{
		$array = [];
		foreach ($this->filters as $filter) {
			$array[] = $filter->getArray();
		}

		$array["quality"] = $this->quality;
		$array["extension"] = $this->extension;

		return $array;
	}
}
