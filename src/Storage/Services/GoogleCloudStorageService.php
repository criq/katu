<?php

namespace Katu\Storage\Services;

use Katu\Storage\StorageService;

class GoogleCloudStorageService extends StorageService
{
	private $bucket;

	public function __construct(\Google\Cloud\Storage\Bucket $bucket)
	{
		$this->setBucket($bucket);
	}

	public function getIsLocal(): bool
	{
		return false;
	}

	public function getIsCloud(): bool
	{
		return true;
	}

	public function setBucket(\Google\Cloud\Storage\Bucket $bucket): GoogleCloudStorageService
	{
		$this->bucket = $bucket;

		return $this;
	}

	public function getBucket(): \Google\Cloud\Storage\Bucket
	{
		return $this->bucket;
	}

	public function getName(): string
	{
		return $this->bucket->name();
	}

	public function getObjectIterator(?string $prefix = null): iterable
	{
		$options = [
			"fields" => implode(",", [
				"items/bucket",
				"items/contentType",
				"items/name",
				"items/size",
				"items/timeCreated",
				"items/updated",
				"nextPageToken",
			]),
		];

		if ($prefix !== null) {
			$options["prefix"] = $prefix;
		}

		foreach ($this->getBucket()->objects($options) as $storageObject) {
			$info = $storageObject->info();
			yield new GoogleCloudStorageObject($this, $storageObject->name(), $info);
		}
	}

	public function readPath(string $path): string
	{
		return $this->getBucket()->object($path)->downloadAsString();
	}

	public function writePath(string $path, string $contents): GoogleCloudStorageObject
	{
		$this->getBucket()->upload($contents, [
			"name" => $path,
		]);

		return new GoogleCloudStorageObject($this, $path);
	}

	public function deleteByPath(string $path): bool
	{
		try {
			$this->getBucket()->object($path)->delete();
			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getIsCompatibleWithURI(string $uri): bool
	{
		if (strpos($uri, "gcs://") !== 0) {
			return false;
		}

		// Extract bucket name from URI: gcs://bucket/path/to/file
		$pathAfterScheme = substr($uri, 6); // Remove "gcs://" prefix
		$firstSlashPos = strpos($pathAfterScheme, "/");

		if ($firstSlashPos === false) {
			// No path, just bucket name
			$bucketName = $pathAfterScheme;
		} else {
			$bucketName = substr($pathAfterScheme, 0, $firstSlashPos);
		}

		return $bucketName === $this->getName();
	}

	public function extractPathFromURI(string $uri): string
	{
		if (strpos($uri, "gcs://") !== 0) {
			throw new \InvalidArgumentException("URI must start with 'gcs://'");
		}

		// Extract path from URI: gcs://bucket/path/to/file
		$pathAfterScheme = substr($uri, 6); // Remove "gcs://" prefix
		$firstSlashPos = strpos($pathAfterScheme, "/");

		if ($firstSlashPos === false) {
			throw new \InvalidArgumentException("URI must include a path after bucket name");
		}

		return substr($pathAfterScheme, $firstSlashPos + 1);
	}

	public function getObjectByURI(string $uri): GoogleCloudStorageObject
	{
		if (!$this->getIsCompatibleWithURI($uri)) {
			throw new \InvalidArgumentException("This service cannot handle the given URI: {$uri}");
		}

		$path = $this->extractPathFromURI($uri);

		return new GoogleCloudStorageObject($this, $path);
	}
}
