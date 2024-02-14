<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Entity;
use Katu\Tools\Package\Package;
use Katu\Types\TFileSize;

class FilesystemEntity extends Entity
{
	protected $path;

	public function __construct(Filesystem $storage, string $path)
	{
		$this->setStorage($storage);
		$this->setPath($path);
	}

	public function getPackage(): Package
	{
	}

	public function getURI(): string
	{
		return realpath($this->getPath());
	}

	public function setPath(string $path): FilesystemEntity
	{
		$this->path = $path;

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getContentType(): ?string
	{
		clearstatcache();

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->getPath());
		finfo_close($finfo);

		return $mime;
	}

	public function getFileSize(): TFileSize
	{
		clearstatcache();

		return new \Katu\Types\TFileSize(filesize($this->getPath()));
	}
}
