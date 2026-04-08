<?php

namespace Katu\Storage\Services;

use Katu\Storage\StorageObject;
use Katu\Storage\StorageService;
use Katu\Tools\Calendar\Time;
use Katu\Types\TFileSize;
use Katu\Types\TURL;
use Psr\Http\Message\StreamInterface;

class GoogleCloudStorageObject extends StorageObject
{
	private $storageObject;
	private $storageObjectInfo;
	private $isPublic;
	private $localFile;

	public function __construct(StorageService $service, string $path, ?array $info = null)
	{
		parent::__construct($service, $path);
		if ($info !== null) {
			$this->setStorageObjectInfo($info);
		}
	}

	public function getStorageObject(): \Google\Cloud\Storage\StorageObject
	{
		if ($this->storageObject === null) {
			$service = $this->getService();
			if (!$service instanceof GoogleCloudStorageService) {
				throw new \RuntimeException("Service must be GoogleCloudStorageService.");
			}

			$this->storageObject = $service->getBucket()->object($this->getPath());
		}

		return $this->storageObject;
	}

	public function setStorageObjectInfo(array $info): GoogleCloudStorageObject
	{
		$this->storageObjectInfo = $info;

		if (isset($info["acl"]) && is_array($info["acl"])) {
			$this->isPublic = (bool)count(array_filter($info["acl"], function (array $aclEntry) {
				return ($aclEntry["entity"] ?? null) == "allUsers" && in_array($aclEntry["role"], ["READER", "OWNER"]);
			}));
		}

		return $this;
	}

	public function getStorageObjectInfo(): array
	{
		if ($this->storageObjectInfo === null) {
			$this->storageObjectInfo = $this->getStorageObject()->info();
		}

		return $this->storageObjectInfo;
	}

	public function getURI(): string
	{
		return "gcs://{$this->getService()->getName()}/{$this->getPath()}";
	}

	public function getType(): ?string
	{
		return $this->getStorageObjectInfo()["contentType"] ?? null;
	}

	public function getSize(): TFileSize
	{
		$size = $this->getStorageObjectInfo()["size"] ?? 0;

		return new TFileSize($size);
	}

	public function exists(): bool
	{
		try {
			return $this->getStorageObject()->exists();
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function isReadable(): bool
	{
		return $this->exists();
	}

	public function isWritable(): bool
	{
		// If we can check existence, we likely have write permissions
		// GCS permissions are typically at bucket level, not object level
		return true;
	}

	public function read(): string
	{
		$service = $this->getService();
		if (!$service instanceof GoogleCloudStorageService) {
			throw new \RuntimeException("Service must be GoogleCloudStorageService.");
		}

		return $service->readPath($this->getPath());
	}

	public function write(string $contents): StorageObject
	{
		$service = $this->getService();
		if (!$service instanceof GoogleCloudStorageService) {
			throw new \RuntimeException("Service must be GoogleCloudStorageService.");
		}

		$service->writePath($this->getPath(), $contents);
		// Invalidate cached info since object was updated
		$this->storageObjectInfo = null;
		$this->storageObject = null;
		$this->isPublic = null;
		$this->localFile = null;

		return $this;
	}

	public function delete(): bool
	{
		$service = $this->getService();
		if (!$service instanceof GoogleCloudStorageService) {
			throw new \RuntimeException("Service must be GoogleCloudStorageService.");
		}

		$result = $service->deleteByPath($this->getPath());
		if ($result) {
			// Invalidate cached info since object was deleted
			$this->storageObjectInfo = null;
			$this->storageObject = null;
			$this->isPublic = null;
			$this->localFile = null;
		}

		return $result;
	}

	public function getIsPublic(): bool
	{
		if ($this->isPublic !== null) {
			return $this->isPublic;
		}

		try {
			$acl = $this->getStorageObject()->acl()->get();
			$this->isPublic = (bool)count(array_filter($acl, function (array $aclEntry) {
				return ($aclEntry["entity"] ?? null) == "allUsers" && in_array($aclEntry["role"], ["READER", "OWNER"]);
			}));
		} catch (\Throwable $e) {
			// Nevermind.
			$this->isPublic = false;
		}

		return $this->isPublic;
	}

	public function getPublicURL(): ?TURL
	{
		$name = rawurlencode($this->getPath());

		return $this->getIsPublic() ? new TURL("https://storage.googleapis.com/{$this->getStorageObjectInfo()["bucket"]}/{$name}") : null;
	}

	public function getFile(): ?\Katu\Files\File
	{
		if ($this->localFile !== null && $this->localFile->exists()) {
			return $this->localFile;
		}

		try {
			$bucket = $this->getStorageObjectInfo()["bucket"];
			$path = $this->getPath();
			$cachedFile = new \Katu\Files\File(\App\App::getTemporaryDir(), "storage-cache", $bucket, $path);

			// Ensure directory exists
			$dir = dirname($cachedFile->getPath());
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}

			// Download and cache the file
			$contents = $this->read();
			$cachedFile->set($contents);

			$this->localFile = $cachedFile;

			return $this->localFile;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getStream(): StreamInterface
	{
		// If file is already cached, use the cached file stream (more efficient than streaming from GCS)
		if ($this->localFile !== null && $this->localFile->exists()) {
			return $this->localFile->getStream("r");
		}

		// Otherwise, stream directly from GCS (avoids downloading if not needed)
		try {
			$storageObject = $this->getStorageObject();

			return $storageObject->downloadAsStream();
		} catch (\Throwable $e) {
			// Fall back to cached file if direct streaming fails
			$file = $this->getFile();

			return $file->getStream("r");
		}
	}

	public function getTimeModified(): ?Time
	{
		$info = $this->getStorageObjectInfo();

		if (!isset($info["updated"])) {
			return null;
		}

		// GCS returns RFC 3339 format (e.g., "2025-01-01T12:00:00.000Z")
		$dateTime = \DateTime::createFromFormat(\DateTime::RFC3339, $info["updated"]);
		if ($dateTime === false) {
			// Try ISO 8601 as fallback
			$dateTime = \DateTime::createFromFormat(\DateTime::ATOM, $info["updated"]);
		}

		if ($dateTime === false) {
			return null;
		}

		return Time::createFromDateTime($dateTime);
	}

	public function getTimeCreated(): ?Time
	{
		$info = $this->getStorageObjectInfo();

		if (isset($info["timeCreated"])) {
			// GCS returns RFC 3339 format (e.g., "2025-01-01T12:00:00.000Z")
			$dateTime = \DateTime::createFromFormat(\DateTime::RFC3339, $info["timeCreated"]);
			if ($dateTime === false) {
				// Try ISO 8601 as fallback
				$dateTime = \DateTime::createFromFormat(\DateTime::ATOM, $info["timeCreated"]);
			}

			if ($dateTime !== false) {
				return Time::createFromDateTime($dateTime);
			}
		}

		// Fallback to updated time if timeCreated is not available
		return $this->getTimeModified();
	}

	public function copyTo(StorageService $destinationService, string $destinationPath): StorageObject
	{
		if (!$this->exists()) {
			throw new \RuntimeException("Source object does not exist: {$this->getPath()}");
		}

		$contents = $this->read();

		// For GCS destination, try to preserve content type
		if ($destinationService instanceof GoogleCloudStorageService) {
			$contentType = $this->getType();
			$bucket = $destinationService->getBucket();

			if ($contentType) {
				$bucket->upload($contents, [
					"name" => $destinationPath,
					"metadata" => [
						"contentType" => $contentType,
					],
				]);
			} else {
				$bucket->upload($contents, [
					"name" => $destinationPath,
				]);
			}

			return new GoogleCloudStorageObject($destinationService, $destinationPath);
		} else {
			// For other services, use standard writePath
			$destinationService->writePath($destinationPath, $contents);

			// Create appropriate StorageObject based on destination service type
			if ($destinationService instanceof LocalStorageService) {
				return new LocalStorageObject($destinationService, $destinationPath);
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
	}

	public function moveTo(StorageService $destinationService, string $destinationPath): StorageObject
	{
		$copiedObject = $this->copyTo($destinationService, $destinationPath);

		$this->delete();

		return $copiedObject;
	}
}
