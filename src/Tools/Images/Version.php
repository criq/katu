<?php

namespace Katu\Tools\Images;

use App\Config\ImageConfig;

class Version
{
	protected $extension;
	protected $filters;
	protected $quality;
	protected $title;

	public function __construct(?string $title = null, ?string $extension = "jpg", ?int $quality = 100, ?FilterCollection $filters = null)
	{
		$this->setExtension($extension);
		$this->setFilters($filters);
		$this->setQuality($quality);
		$this->setTitle($title);
	}

	public static function createFromCode($code): ?Version
	{
		return (new ImageConfig)->getVersions()->filterByTitle($code)->getFirst();
	}

	public function setTitle(?string $title): Version
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
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

	public function setQuality(int $quality): Version
	{
		$this->quality = $quality;

		return $this;
	}

	public function getQuality(): int
	{
		return $this->quality;
	}

	public function setFilters(?FilterCollection $filters): Version
	{
		$this->filters = $filters;

		return $this;
	}

	public function getFilters(): ?FilterCollection
	{
		return $this->filters;
	}













	public function getHash(): string
	{
		return sha1(\Katu\Files\Formats\JSON::encodeStandard($this->getArray()));
	}






	public function getDir(): \Katu\Files\File
	{
		$dir = new \Katu\Files\File(\App\App::getPublicTemporaryDir(), "images", "versions", $this->getTitle(), $this->getHash());
		if (!$dir->isWritable()) {
			try {
				$dir->makeDir();
			} catch (\Throwable $e) {
				throw new \Katu\Exceptions\ErrorException("Can't create image version folder at {$dir}.");
			}
		}

		return $dir;
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
