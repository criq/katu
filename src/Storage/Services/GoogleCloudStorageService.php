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

	public function getFingerprintArray(): array
	{
		return [
			static::class,
			$this->getBucket()->name(),
		];
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
		// Check for gcs:// or gs:// URIs
		if (strpos($uri, "gcs://") === 0 || strpos($uri, "gs://") === 0) {
			// Extract bucket name from URI: gcs://bucket/path/to/file or gs://bucket/path/to/file
			$pathAfterScheme = $this->removeSchemeFromURI($uri);
			$firstSlashPos = strpos($pathAfterScheme, "/");

			if ($firstSlashPos === false) {
				// No path, just bucket name
				$bucketName = $pathAfterScheme;
			} else {
				$bucketName = substr($pathAfterScheme, 0, $firstSlashPos);
			}

			return $bucketName === $this->getName();
		}

		// Check for Google Cloud Storage REST API URLs
		if ($this->isRestAPIURL($uri)) {
			$bucketName = $this->extractBucketFromRestAPIURL($uri);
			return $bucketName === $this->getName();
		}

		return false;
	}

	public function extractPathFromURI(string $uri): string
	{
		// Handle gcs:// or gs:// URIs
		if (strpos($uri, "gcs://") === 0 || strpos($uri, "gs://") === 0) {
			// Extract path from URI: gcs://bucket/path/to/file or gs://bucket/path/to/file
			$pathAfterScheme = $this->removeSchemeFromURI($uri);
			$firstSlashPos = strpos($pathAfterScheme, "/");

			if ($firstSlashPos === false) {
				throw new \InvalidArgumentException("URI must include a path after bucket name");
			}

			return substr($pathAfterScheme, $firstSlashPos + 1);
		}

		// Handle Google Cloud Storage REST API URLs
		if ($this->isRestAPIURL($uri)) {
			return $this->extractPathFromRestAPIURL($uri);
		}

		throw new \InvalidArgumentException("URI must start with 'gcs://', 'gs://', or be a valid Google Cloud Storage REST API URL");
	}

	private function removeSchemeFromURI(string $uri): string
	{
		if (strpos($uri, "gcs://") === 0) {
			return substr($uri, 6); // Remove "gcs://" prefix
		}
		if (strpos($uri, "gs://") === 0) {
			return substr($uri, 5); // Remove "gs://" prefix
		}

		return $uri;
	}

	private function isRestAPIURL(string $uri): bool
	{
		// Check for Google Cloud Storage REST API URL format:
		// https://www.googleapis.com/storage/v1/b/{bucket}/o/{object}
		return (bool)preg_match("/^https:\/\/www\.googleapis\.com\/storage\/v1\/b\/[^\/]+\/o\/.+/", $uri);
	}

	private function extractBucketFromRestAPIURL(string $uri): ?string
	{
		if (!preg_match("/^https:\/\/www\.googleapis\.com\/storage\/v1\/b\/(?<bucket>[^\/]+)\/o\//", $uri, $matches)) {
			return null;
		}

		return $matches["bucket"];
	}

	private function extractPathFromRestAPIURL(string $uri): string
	{
		if (!preg_match("/^https:\/\/www\.googleapis\.com\/storage\/v1\/b\/[^\/]+\/o\/(?<object>.+)/", $uri, $matches)) {
			throw new \InvalidArgumentException("Invalid Google Cloud Storage REST API URL format");
		}

		// The object path is URL-encoded, so we need to decode it
		return rawurldecode($matches["object"]);
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
