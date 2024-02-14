<?php

namespace Katu\Storage;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;
use Katu\Types\TFileSize;
use Katu\Types\TIdentifier;

abstract class Entity implements PackagedInterface
{
	protected $storage;
	protected $storageObject;

	abstract public function getContentType(): ?string;
	abstract public function getFileSize(): TFileSize;
	abstract public function getURI(): string;

	public static function createFromPackage(Package $package): Entity
	{
		$className = TClass::createFromPortableName($package->getPayload()["class"])->getName();

		return $className::createFromPackage($package);
	}

	public function setStorage(Storage $storage): Entity
	{
		$this->storage = $storage;

		return $this;
	}

	public function getStorage(): Storage
	{
		return $this->storage;
	}

	public function setStorageObject(\Google\Cloud\Storage\StorageObject $storageObject): Entity
	{
		$this->storageObject = $storageObject;

		return $this;
	}

	public function getStorageObject(): \Google\Cloud\Storage\StorageObject
	{
		return $this->storageObject;
	}

	public function write($content): Entity
	{
		$this->getStorage()->write($this, $content);

		return $this;
	}

	public function read()
	{
		return $this->getStorage()->read($this);
	}

	public function delete(): bool
	{
		return $this->getStorage()->delete($this);
	}

	public function getFileName(): ?string
	{
		return pathinfo($this->getURI())["basename"] ?? null;
	}

	public function getIsImage(): bool
	{
		return strpos($this->getContentType(), "image/") === 0;
	}

	public function getImage(): ?\Katu\Tools\Images\Image
	{
		return new \Katu\Tools\Images\Image(new \Katu\Tools\Images\Sources\File($this->getLocalFile()));
	}

	public function getLocalFile(): \Katu\Files\File
	{
		$extension = pathinfo($this->getURI())["extension"] ?? null;
		$identifier = new TIdentifier(__CLASS__, __FUNCTION__, sha1($this->getURI()));
		$file = new \Katu\Files\File(\App\App::getTemporaryDir(), $identifier->getPath($extension));
		if (!$file->exists()) {
			$file->set($this->read());
		}

		return $file;
	}
}
