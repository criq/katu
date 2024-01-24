<?php

namespace Katu\Files;

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
		return new static(\App\App::getTemporaryDir(), "files", static::prepareFileName($fileName));
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

	public static function createTemporaryFromURL($url, string $extension = null): ?File
	{
		$url = new \Katu\Types\TURL($url);

		$curl = new \Curl\Curl;
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		$curl->setOpt(CURLOPT_RETURNTRANSFER, true);

		$src = $curl->get($url);

		$info = $curl->getInfo();
		if ($info["http_code"] != 200) {
			return null;
		}

		if (!$extension && ($url->getParts()["path"] ?? null)) {
			$extension = pathinfo($url->getParts()["path"])["extension"];
		}

		return static::createTemporaryFromSrc($src, $extension);
	}

	public function getURL(): ?TURL
	{
		try {
			$publicRoot = \Katu\Config\Config::get("app", "publicRoot");
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$publicRoot = "./public/";
		}

		try {
			$publicPath = realpath(new static(\App\App::getBaseDir(), $publicRoot));
			if (preg_match("/^" . preg_quote($publicPath, "/") . "(.*)$/", (string)$this->getPath(), $match)) {
				return new TURL(implode("/", array_map(function ($i) {
					return trim($i, "/");
				}, array_filter([
					\Katu\Config\Config::get("app", "baseUrl"),
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

	public function get()
	{
		try {
			return @file_get_contents($this);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getLines()
	{
		try {
			return file($this);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function set($data)
	{
		try {
			$this->getDir()->makeDir();
			return file_put_contents($this, $data, LOCK_EX);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function append($data)
	{
		$this->touch();

		return file_put_contents($this, $data, LOCK_EX | FILE_APPEND);
	}

	public function getType()
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

		return false;
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

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->getPath());
		finfo_close($finfo);

		return $mime;
	}

	public function getPathInfo()
	{
		return pathinfo($this->getPath());
	}

	public function getExtension()
	{
		$pathinfo = $this->getPathInfo();

		if (isset($pathinfo["extension"])) {
			return $pathinfo["extension"];
		}

		return false;
	}

	public function getDir()
	{
		return new self(dirname($this));
	}

	public function getBasename()
	{
		return basename($this);
	}

	public function getFiles($filters = [])
	{
		$files = [];

		foreach (scandir($this) as $file) {
			$file = new static($this, $file);
			if ($file->isFile()) {
				$files[] = $file;
			}
		}

		if (isset($filters["regexp"])) {
			$files = array_filter($files, function ($i) use ($filters) {
				return preg_match($filters["regexp"], $i);
			});
		}

		return $files;
	}

	public function getDirs()
	{
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

	public function isFile()
	{
		return $this->getType() == static::TYPE_FILE;
	}

	public function isDir()
	{
		return $this->getType() == static::TYPE_DIR;
	}

	public function isPhpFile()
	{
		return $this->isFile() && ($this->getMime() == "text/x-c++" || $this->getExtension() == "php");
	}

	public function isReadable()
	{
		return is_readable($this);
	}

	public function isWritable()
	{
		return is_writable($this);
	}

	public function makeDir($mode = 0777, $recursive = true)
	{
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

	public function chmod($mode)
	{
		return chmod($this, $mode);
	}

	public function copy(File $destination)
	{
		if (!$this->exists()) {
			throw (new \Katu\Exceptions\ErrorException("Source file doesn't exist."))
				->setAbbr("sourceFileUnavailable")
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

	public function move(File $destination)
	{
		$this->copy($destination);
		$this->delete();

		return true;
	}

	public function delete()
	{
		clearstatcache();

		if ($this->isDir()) {
			$it = new \RecursiveDirectoryIterator((string) $this, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $file) {
				if ($file->isDir()) {
					rmdir($file->getRealPath());
				} else {
					unlink($file->getRealPath());
				}
			}

			return rmdir((string) $this);
		} else {
			return unlink((string) $this);
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

	public function eachRecursive($callback)
	{
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

	public function getHash($function = "sha1")
	{
		return hash($function, $this->get());
	}

	public function getHashedURL(?string $algo = "sha1", ?string $paramName = "hash")
	{
		if (!$algo) {
			$algo = "sha1";
		}

		return (new \Katu\Types\TURL($this->getURL()))
			->addQueryParam($paramName, $this->getHash($algo))
			;
	}

	public function getCachedHashedURL(Timeout $timeout = null, ?string $algo = "sha1", ?string $paramName = "hash")
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__), $timeout, function ($file, $algo, $paramName) {
			return $file->getHashedURL($algo, $paramName);
		}, $this, $algo, $paramName);
	}

	public function getStream(): StreamInterface
	{
		return \GuzzleHttp\Psr7\Utils::streamFor(fopen((string)$this, "a+"));
	}
}
