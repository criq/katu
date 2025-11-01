<?php

namespace Katu\Storage;

use Katu\Tools\Calendar\Time;
use Katu\Types\TFileSize;
use Psr\Http\Message\StreamInterface;

abstract class StorageObject
{
	private $service;
	private $path;

	abstract public function copyTo(StorageService $destinationService, string $destinationPath): StorageObject;
	abstract public function delete(): bool;
	abstract public function exists(): bool;
	abstract public function getFile(): \Katu\Files\File;
	abstract public function getSize(): TFileSize;
	abstract public function getStream(): StreamInterface;
	abstract public function getTimeCreated(): ?Time;
	abstract public function getTimeModified(): ?Time;
	abstract public function getType(): ?string;
	abstract public function getURI(): string;
	abstract public function isReadable(): bool;
	abstract public function isWritable(): bool;
	abstract public function moveTo(StorageService $destinationService, string $destinationPath): StorageObject;
	abstract public function read(): string;
	abstract public function write(string $contents): StorageObject;

	public function __construct(StorageService $service, string $path)
	{
		$this->setService($service);
		$this->setPath($path);
	}

	public function setService(StorageService $service): StorageObject
	{
		$this->service = $service;

		return $this;
	}

	public function getService(): StorageService
	{
		return $this->service;
	}

	public function setPath(string $path): StorageObject
	{
		$this->path = $path;

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getName(): string
	{
		return basename($this->getPath());
	}

	public function getExtension(): string
	{
		$extension = pathinfo($this->getPath(), PATHINFO_EXTENSION);

		return strtolower($extension);
	}

	public function getDirectory(): string
	{
		$directory = dirname($this->getPath());

		return $directory === "." ? "" : $directory;
	}
}
