<?php

namespace Katu\Storage;

abstract class StorageService
{
	private $isWritable = true;

	abstract public function deleteByPath(string $path): bool;
	abstract public function getFingerprintArray(): array;
	abstract public function getIsCloud(): bool;
	abstract public function getIsCompatibleWithURI(string $uri): bool;
	abstract public function getIsLocal(): bool;
	abstract public function getName(): string;
	abstract public function getObjectByURI(string $uri): StorageObject;
	abstract public function getObjectIterator(?string $prefix = null): iterable;
	abstract public function readPath(string $path): string;
	abstract public function writePath(string $path, string $contents): StorageObject;

	public function getFingerprint(): string
	{
		return sha1(implode(":", $this->getFingerprintArray()));
	}

	public function setIsWritable(bool $isWritable): StorageService
	{
		$this->isWritable = $isWritable;

		return $this;
	}

	public function getIsWritable(): bool
	{
		return $this->isWritable;
	}

	public function getObjects(?string $prefix = null): StorageObjectCollection
	{
		return new StorageObjectCollection(iterator_to_array($this->getObjectIterator($prefix)));
	}

}
