<?php

namespace Katu\Storage\Services;

use Katu\Storage\StorageService;

class LocalStorageService extends StorageService
{
	private $path;

	public function __construct(string $path)
	{
		$this->setPath($path);
	}

	public function getFingerprintArray(): array
	{
		return [
			static::class,
			$this->getPath(),
		];
	}

	public function getName(): string
	{
		return $this->getPath();
	}

	public function getIsLocal(): bool
	{
		return true;
	}

	public function getIsCloud(): bool
	{
		return false;
	}

	public function setPath(string $path): LocalStorageService
	{
		$this->path = $path;

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getObjectIterator(?string $prefix = null): iterable
	{
		$baseDirectory = rtrim($this->getPath(), DIRECTORY_SEPARATOR);

		if (!is_dir($baseDirectory)) {
			return [];
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($baseDirectory, \FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $fileInfo) {
			if (!$fileInfo->isFile()) {
				continue;
			}

			$fullPath = $fileInfo->getPathname();
			$relativePath = str_replace($baseDirectory . DIRECTORY_SEPARATOR, "", $fullPath);
			$relativePath = str_replace("\\", "/", $relativePath);

			// Filter by prefix if provided
			if ($prefix !== null && strpos($relativePath, $prefix) !== 0) {
				continue;
			}

			yield new LocalStorageObject($this, $relativePath);
		}
	}

	public function getFullPath(string $path): string
	{
		$baseDirectory = rtrim($this->getPath(), DIRECTORY_SEPARATOR);
		$relativePath = str_replace("/", DIRECTORY_SEPARATOR, $path);

		return $baseDirectory . DIRECTORY_SEPARATOR . $relativePath;
	}

	public function readPath(string $path): string
	{
		$fullPath = $this->getFullPath($path);
		$contents = file_get_contents($fullPath);

		if ($contents === false) {
			throw new \RuntimeException("Failed to read file: {$path}");
		}

		return $contents;
	}

	public function writePath(string $path, string $contents): LocalStorageObject
	{
		$fullPath = $this->getFullPath($path);
		$directory = dirname($fullPath);

		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}

		$result = file_put_contents($fullPath, $contents);

		if ($result === false) {
			throw new \RuntimeException("Failed to write file: {$path}");
		}

		return new LocalStorageObject($this, $path);
	}

	/**
	 * Write a file from a stream (memory-efficient for large files).
	 *
	 * @param string $path Destination path relative to service root
	 * @param resource $stream A readable stream resource
	 * @return LocalStorageObject
	 */
	public function writeStream(string $path, $stream): LocalStorageObject
	{
		$fullPath = $this->getFullPath($path);
		$directory = dirname($fullPath);

		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}

		$destination = fopen($fullPath, "wb");
		if ($destination === false) {
			throw new \RuntimeException("Failed to open file for writing: {$path}");
		}

		try {
			$bytesCopied = stream_copy_to_stream($stream, $destination);
			if ($bytesCopied === false) {
				throw new \RuntimeException("Failed to copy stream to file: {$path}");
			}
		} finally {
			fclose($destination);
		}

		return new LocalStorageObject($this, $path);
	}

	public function deleteByPath(string $path): bool
	{
		$fullPath = $this->getFullPath($path);

		if (!file_exists($fullPath)) {
			return false;
		}

		return unlink($fullPath);
	}

	public function getIsCompatibleWithURI(string $uri): bool
	{
		if (strpos($uri, "local://") !== 0) {
			return false;
		}

		$uriPath = substr($uri, 8); // Remove "local://" prefix
		$fullPath = $this->getFullPath($uriPath);
		$baseDirectory = rtrim($this->getPath(), DIRECTORY_SEPARATOR);

		// Normalize paths for comparison (handle both forward and backslashes)
		$normalizedBase = str_replace("\\", "/", $baseDirectory);
		$normalizedFull = str_replace("\\", "/", $fullPath);

		// Check if the full path starts with the base directory
		return strpos($normalizedFull, $normalizedBase) === 0;
	}

	public function extractPathFromURI(string $uri): string
	{
		if (strpos($uri, "local://") !== 0) {
			throw new \InvalidArgumentException("URI must start with 'local://'");
		}

		return substr($uri, 8); // Remove "local://" prefix
	}

	public function getObjectByURI(string $uri): LocalStorageObject
	{
		if (!$this->getIsCompatibleWithURI($uri)) {
			throw new \InvalidArgumentException("This service cannot handle the given URI: {$uri}");
		}

		$path = $this->extractPathFromURI($uri);

		return new LocalStorageObject($this, $path);
	}
}
