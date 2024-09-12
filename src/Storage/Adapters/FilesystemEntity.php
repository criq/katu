<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Entity;
use Katu\Storage\Storage;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;
use Katu\Types\TFileSize;

class FilesystemEntity extends Entity
{
	protected $path;

	public function __construct(Filesystem $storage, string $path)
	{
		$this->setStorage($storage);
		$this->setPath($path);
	}

	public static function createStorageFromPackage(Package $package): ?Filesystem
	{
		$package = new Package($package->getPayload()["storage"]);

		return Storage::createFromPackage($package);
	}

	public static function createFromPackage(Package $package): ?FilesystemEntity
	{
		$storage = static::createStorageFromPackage($package);

		return new static($storage, $package->getPayload()["path"]);
	}

	public function getStorage(): Filesystem
	{
		return $this->storage;
	}

	public function getPackage(): Package
	{
		return new Package([
			"storage" => $this->getStorage()->getPackage()->getPayload(),
			"class" => (new TClass($this))->getPortableName(),
			"path" => $this->getPath(),
		]);
	}

	public function getURI(): string
	{
		return realpath(implode("/", [
			rtrim($this->getStorage()->getRoot(), "/"),
			ltrim($this->getPath(), "/"),
		]));
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
		$mime = finfo_file($finfo, $this->getURI());
		finfo_close($finfo);

		return $mime;
	}

	public function getFileSize(): TFileSize
	{
		clearstatcache();

		return new \Katu\Types\TFileSize(filesize($this->getPath()));
	}
}
