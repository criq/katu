<?php

namespace Katu\Files;

use Katu\Storage\FileInterface;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;
use Psr\Http\Message\StreamInterface;

class File
{
	const TYPE_DIR = "dir";
	const TYPE_FILE = "file";

	public $path;

	public function __construct()
	{
		$this->setPath(...func_get_args());
	}

	public function __toString(): string
	{
		return $this->getPath();
	}

	public function setPath(): File
	{
		$this->path = static::joinPaths(...func_get_args());

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getRelativePath(): string
	{
		return ltrim(preg_replace("/^" . preg_quote(\App\App::getBaseDir(), "/") . "/", "", $this->getPath()), "/");
	}

	public static function joinPaths(): string
	{
		return preg_replace("/(\/)+/", "/", implode("/", array_map(function ($i) {
			return implode(".", (array)$i);
		}, func_get_args())));
	}

	public static function prepareFileName(string $fileName): string
	{
		return preg_replace_callback("/\{(?<length>[0-9+])\}/", function ($i) {
			return \Katu\Tools\Random\Generator::getIdString($i["length"]);
		}, $fileName);
	}

	public static function createTemporaryWithFileName(string $fileName): File
	{
		return new static(\App\App::getTemporaryDir(), "files", \Katu\Tools\Random\Generator::getFileName(), static::prepareFileName($fileName));
	}

	public static function createTemporaryWithExtension(string $extension): File
	{
		return new static(\App\App::getTemporaryDir(), "files", [\Katu\Tools\Random\Generator::getFileName(), $extension]);
	}

	public static function createTemporaryFromSrc(string $src, string $extension): File
	{
		if ($extension) {
			$file = static::createTemporaryWithExtension($extension);
		} else {
			$file = static::createTemporaryWithFileName(\Katu\Tools\Random\Generator::getFileName());
		}

		$file->set($src);

		return $file;
	}

	public static function createTemporaryFromURL($url, ?string $extension = null, int $timeout = 30): ?File
	{
		$url = new \Katu\Types\TURL($url);

		$curl = new \Curl\Curl;
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
		$curl->setOpt(CURLOPT_TIMEOUT, $timeout);
		$curl->setOpt(CURLOPT_CONNECTTIMEOUT, 10);
		$curl->setOpt(CURLOPT_MAXREDIRS, 5);

		$src = $curl->get($url);

		$info = $curl->getInfo();
		if ($info["http_code"] != 200) {
			return null;
		}

		if (!$extension && ($url->getParts()["path"] ?? null)) {
			$extension = pathinfo($url->getParts()["path"])["extension"] ?? null;
		}

		return static::createTemporaryFromSrc($src, $extension);
	}

	public function getURL(): ?TURL
	{
		try {
			$publicDir = \App\App::getPublicDir();
			$publicPath = realpath((string) new static(\App\App::getBaseDir(), $publicDir));
			if (preg_match("/^" . preg_quote($publicPath, "/") . "(.*)$/", (string)$this->getPath(), $match)) {
				return new TURL(implode("/", array_map(function ($i) {
					return trim($i, "/");
				}, array_filter([
					\App\App::getAppConfig()->getBaseURL(),
					$match[1],
				]))));
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return null;
	}

	public function exists(): bool
	{
		clearstatcache();

		return file_exists($this->getPath());
	}

	/**
	 * @return string|false
	 */
	public function get()
	{
		if (!$this->exists()) {
			return false;
		}

		try {
			return @file_get_contents($this);
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * @return array|false
	 */
	public function getLines()
	{
		if (!$this->exists()) {
			return false;
		}

		try {
			return @file($this);
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * @return int|false
	 */
	public function set($data)
	{
		try {
			$this->getDir()->makeDir();
			return @file_put_contents($this, $data, LOCK_EX);
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * @return int|false
	 */
	public function append($data)
	{
		$this->touch();

		return @file_put_contents($this, $data, LOCK_EX | FILE_APPEND);
	}

	public function getType(): ?string
	{
		clearstatcache();

		if (!$this->exists()) {
			throw new \Katu\Exceptions\FileNotFoundException;
		}

		if (is_file($this->getPath())) {
			return static::TYPE_FILE;
		} elseif (is_dir($this->getPath())) {
			return static::TYPE_DIR;
		}

		return null;
	}

	public function getSize(): ?\Katu\Types\TFileSize
	{
		clearstatcache();

		try {
			return new \Katu\Types\TFileSize(filesize($this));
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getMime(): ?string
	{
		clearstatcache();

		if (!$this->exists()) {
			return null;
		}

		if (!function_exists("finfo_open")) {
			return null;
		}

		$finfo = @finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo === false) {
			return null;
		}

		$mime = @finfo_file($finfo, $this->getPath());
		finfo_close($finfo);

		return $mime ?: null;
	}

	public function getPathInfo(): array
	{
		return pathinfo($this->getPath());
	}

	public function getFilename(): ?string
	{
		return $this->getPathInfo()["filename"] ?? null;
	}

	public function getExtension(): ?string
	{
		return $this->getPathInfo()["extension"] ?? null;
	}

	public function getDir(): self
	{
		return new self(dirname($this));
	}

	public function getBasename(): string
	{
		return basename($this);
	}

	public function getFiles(): FileCollection
	{
		$files = new FileCollection;

		foreach (scandir($this) as $file) {
			$file = new static($this, $file);
			if ($file->isFile()) {
				$files[] = $file;
			}
		}

		return $files;
	}

	public function getDirs(): array
	{
		if (!$this->isDir()) {
			return [];
		}

		$files = [];

		foreach (scandir($this) as $file) {
			if ($file != "." && $file != "..") {
				$file = new static($this, $file);
				if ($file->isDir()) {
					$files[] = $file;
				}
			}
		}

		return $files;
	}

	public function isFile(): bool
	{
		if (!$this->exists()) {
			return false;
		}

		try {
			return $this->getType() == static::TYPE_FILE;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function isDir(): bool
	{
		if (!$this->exists()) {
			return false;
		}

		try {
			return $this->getType() == static::TYPE_DIR;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function isPhpFile(): bool
	{
		if (!$this->isFile()) {
			return false;
		}

		$mime = $this->getMime();
		$extension = $this->getExtension();

		return ($mime == "text/x-php" || $mime == "text/x-c++") || strtolower($extension ?? "") == "php";
	}

	public function isReadable(): bool
	{
		return $this->exists() && is_readable($this);
	}

	public function isWritable(): bool
	{
		if ($this->exists()) {
			return is_writable($this);
		}

		// If file doesn't exist, check if parent directory is writable
		return $this->getDir()->isWritable();
	}

	public function makeDir(int $mode = 0777, bool $recursive = true): bool
	{
		if ($this->exists() && $this->isDir()) {
			return true;
		}

		try {
			return @mkdir($this, $mode, $recursive);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function touch(): File
	{
		$dir = $this->getDir();
		$dir->makeDir();

		touch($this);

		return $this;
	}

	public function chmod(int $mode): bool
	{
		if (!$this->exists()) {
			return false;
		}

		return @chmod($this, $mode);
	}

	public function copy(File $destination): File
	{
		if (!$this->exists()) {
			throw (new \Katu\Exceptions\ErrorException("Source file doesn't exist."))
				->setAbbr("sourceFileUnavailable")
				;
		}

		if ($this->isDir()) {
			throw (new \Katu\Exceptions\ErrorException("Cannot copy directory using copy() method. Use recursive copy instead."))
				->setAbbr("directoryCopyNotSupported")
				;
		}

		$destination->touch();
		if (!copy($this, $destination)) {
			throw (new \Katu\Exceptions\ErrorException("Couldn't copy the file."))
				->setAbbr("fileCopyFailed")
				;
		}

		return $destination;
	}

	public function move(File $destination): bool
	{
		$this->copy($destination);

		if (!$this->delete()) {
			// If delete fails after copy, attempt to remove the copied file
			try {
				$destination->delete();
			} catch (\Throwable $e) {
				// Log but don't throw - the original delete failure is the main issue
			}
			throw new \Katu\Exceptions\ErrorException("Could not delete source file after copy: " . $this->getPath());
		}

		return true;
	}

	public function delete(): bool
	{
		if (!$this->exists()) {
			return true;
		}

		clearstatcache();

		if ($this->isDir()) {
			$it = new \RecursiveDirectoryIterator((string) $this, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $file) {
				if ($file->isDir()) {
					@rmdir($file->getRealPath());
				} else {
					@unlink($file->getRealPath());
				}
			}

			return @rmdir((string) $this);
		} else {
			return @unlink((string) $this);
		}
	}

	public function getModifiedTime(): ?Time
	{
		try {
			if ($this->exists()) {
				return new Time("@" . filemtime((string)$this));
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return null;
	}

	public function eachRecursive(callable $callback): void
	{
		if (!$this->isDir()) {
			return;
		}

		$iterator = new \RecursiveDirectoryIterator($this, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file) {
			call_user_func_array($callback, [new static((string) $file)]);
		}
	}

	public function includeOnce()
	{
		return include_once $this;
	}

	public function includeAllPhpFiles()
	{
		return $this->eachRecursive(function ($i) {
			if ($i->isPhpFile()) {
				$i->includeOnce();
			}
		});
	}

	public static function getHashedFiles(): array
	{
		clearstatcache();

		$placeholderFile = new \Katu\Files\File(...func_get_args());
		$platformDir = new \Katu\Files\File(preg_replace("/{platform}/", \Katu\Config\Env::getPlatform(), $placeholderFile->getDir()));

		$fileRegexp = $placeholderFile->getBasename();
		$fileRegexp = preg_replace("/{hash}/", "([0-9a-f]+)?", $fileRegexp);
		$fileRegexp = preg_replace("/{dash}/", "-?", $fileRegexp);
		$fileRegexp = "/^" . $fileRegexp . "$/";

		$matchedFiles = [];
		foreach ($platformDir->getFiles() as $file) {
			if (preg_match($fileRegexp, $file->getBasename())) {
				$matchedFiles[] = $file;
			}
		}

		usort($matchedFiles, function ($a, $b) {
			return filemtime($a) > filemtime($b) ? -1 : 1;
		});

		foreach (array_slice($matchedFiles, 1) as $file) {
			$file->delete();
		}

		return array_slice($matchedFiles, 0, 1);
	}

	public function getHash(string $function = "sha1"): string
	{
		if (!in_array($function, hash_algos(), true)) {
			throw new \Katu\Exceptions\ErrorException("Invalid hash algorithm: " . $function);
		}

		// For large files, use incremental hashing to avoid memory issues
		if ($this->exists() && $this->getSize() && $this->getSize()->getInB()->getAmount() > 10 * 1024 * 1024) {
			$handle = fopen((string)$this, "rb");
			if ($handle === false) {
				throw new \Katu\Exceptions\ErrorException("Could not open file for hashing: " . $this->getPath());
			}

			$hash = hash_init($function);
			while (!feof($handle)) {
				hash_update($hash, fread($handle, 8192));
			}
			fclose($handle);

			return hash_final($hash);
		}

		$content = $this->get();
		if ($content === false) {
			throw new \Katu\Exceptions\ErrorException("Could not read file for hashing: " . $this->getPath());
		}

		return hash($function, $content);
	}

	public function getHashedURL(?string $algo = "sha1", ?string $paramName = "hash"): ?TURL
	{
		$url = $this->getURL();
		if (!$url) {
			return null;
		}

		if (!$algo) {
			$algo = "sha1";
		}

		return (new \Katu\Types\TURL($url))
			->addQueryParam($paramName, $this->getHash($algo))
			;
	}

	public function getCachedHashedURL(?Timeout $timeout = null, ?string $algo = "sha1", ?string $paramName = "hash"): ?TURL
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__), $timeout, function ($file, $algo, $paramName) {
			return $file->getHashedURL($algo, $paramName);
		}, $this, $algo, $paramName);
	}

	public static function getSupportedImageTypes(): array
	{
		return [
			"image/gif",
			"image/jpeg",
			"image/png",
			"image/webp",
		];
	}

	public function getIsSupportedImage(): bool
	{
		$mime = $this->getMime();
		return $mime !== null && in_array($mime, static::getSupportedImageTypes(), true);
	}

	public function getStream(string $mode = "r"): StreamInterface
	{
		if (!$this->exists()) {
			throw new \Katu\Exceptions\FileNotFoundException("File does not exist: " . $this->getPath());
		}

		$handle = fopen((string)$this, $mode);
		if ($handle === false) {
			throw new \Katu\Exceptions\ErrorException("Could not open file: " . $this->getPath());
		}

		return \GuzzleHttp\Psr7\Utils::streamFor($handle);
	}
}
