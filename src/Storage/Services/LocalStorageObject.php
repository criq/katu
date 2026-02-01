<?php

namespace Katu\Storage\Services;

use Katu\Storage\StorageObject;
use Katu\Tools\Calendar\Time;
use Katu\Types\TFileSize;

class LocalStorageObject extends StorageObject
{
	public function getURI(): string
	{
		return "local://{$this->getPath()}";
	}

	public function getType(): ?string
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		if (!file_exists($fullPath)) {
			return null;
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, $fullPath);
		finfo_close($finfo);

		return $mimeType ?: null;
	}

	public function exists(): bool
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		return file_exists($fullPath) && is_file($fullPath);
	}

	public function isReadable(): bool
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		return $this->exists() && is_readable($fullPath);
	}

	public function isWritable(): bool
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		if ($this->exists()) {
			return is_writable($fullPath);
		}

		$directory = dirname($fullPath);

		return is_dir($directory) && is_writable($directory);
	}

	public function read(): string
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		return $service->readPath($this->getPath());
	}

	public function write(string $contents): StorageObject
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$service->writePath($this->getPath(), $contents);

		return $this;
	}

	public function delete(): bool
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		return $service->deleteByPath($this->getPath());
	}

	public function getSize(): TFileSize
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		if (!file_exists($fullPath)) {
			return new TFileSize(0);
		}

		return new TFileSize(filesize($fullPath));
	}

	public function getFile(): ?\Katu\Files\File
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		return new \Katu\Files\File($fullPath);
	}

	public function getStream(): \Psr\Http\Message\StreamInterface
	{
		$file = $this->getFile();

		return $file->getStream("r");
	}

	public function getTimeModified(): ?Time
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		if (!file_exists($fullPath)) {
			return null;
		}

		$timestamp = filemtime($fullPath);
		if ($timestamp === false) {
			return null;
		}

		return Time::createFromTimestamp($timestamp);
	}

	public function getTimeCreated(): ?Time
	{
		$service = $this->getService();
		if (!$service instanceof LocalStorageService) {
			throw new \RuntimeException("Service must be LocalStorageService.");
		}

		$fullPath = $service->getFullPath($this->getPath());

		if (!file_exists($fullPath)) {
			return null;
		}

		// On some systems, filectime returns creation time, on others it returns change time
		// Use it as fallback for "created" time
		$timestamp = filectime($fullPath);
		if ($timestamp === false) {
			return null;
		}

		return Time::createFromTimestamp($timestamp);
	}

	public function copyTo(\Katu\Storage\StorageService $destinationService, string $destinationPath): StorageObject
	{
		if (!$this->exists()) {
			throw new \RuntimeException("Source object does not exist: {$this->getPath()}");
		}

		$contents = $this->read();

		$destinationService->writePath($destinationPath, $contents);

		// Create appropriate StorageObject based on destination service type
		if ($destinationService instanceof LocalStorageService) {
			return new LocalStorageObject($destinationService, $destinationPath);
		} elseif ($destinationService instanceof GoogleCloudStorageService) {
			return new GoogleCloudStorageObject($destinationService, $destinationPath);
		} else {
			// Fallback: use URI to create object
			// Try to determine URI scheme from service type
			if ($destinationService instanceof GoogleCloudStorageService) {
				$destinationURI = "gcs://{$destinationService->getName()}/{$destinationPath}";
			} else {
				$destinationURI = "local://{$destinationPath}";
			}

			return $destinationService->getObjectByURI($destinationURI);
		}
	}

	public function moveTo(\Katu\Storage\StorageService $destinationService, string $destinationPath): StorageObject
	{
		$copiedObject = $this->copyTo($destinationService, $destinationPath);

		$this->delete();

		return $copiedObject;
	}
}
