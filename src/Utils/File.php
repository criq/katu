<?php

namespace Katu\Utils;

class File
{
	public $path;

	const TYPE_FILE = 'file';
	const TYPE_DIR  = 'dir';

	public function __construct()
	{
		$this->path = call_user_func_array(['\Katu\Utils\FileSystem', 'joinPaths'], func_get_args());

		return $this;
	}

	public function __toString()
	{
		return $this->getPath();
	}

	public static function createTemporaryWithFileName($fileName)
	{
		$file = new static(TMP_PATH, 'files', $fileName);

		return $file;
	}

	public static function createTemporaryWithExtension($extension)
	{
		$file = new static(TMP_PATH, 'files', [\Katu\Utils\Random::getFileName(), $extension]);

		return $file;
	}

	public static function createTemporaryFromSrc($src, $extension)
	{
		if ($extension) {
			$file = static::createTemporaryWithExtension($extension);
		} else {
			$file = static::createTemporaryWithFileName(\Katu\Utils\Random::getFileName());
		}

		$file->set($src);

		return $file;
	}

	public static function createTemporaryFromUrl($url, $extension = null)
	{
		$url = new \Katu\Types\TUrl($url);

		$curl = new \Curl\Curl;
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);

		$src = $curl->get($url);
		$info = $curl->getInfo();

		if ($info['http_code'] != 200) {
			return false;
		}

		if (!$extension && isset($url->getParts()['path']['extension'])) {
			$extension = pathinfo($url->getParts()['path']['extension']);
		}

		return static::createTemporaryFromSrc($src, $extension);
	}

	public function getPath()
	{
		if (file_exists($this->path)) {
			return realpath($this->path);
		}

		$path = FileSystem::joinPaths(BASE_DIR, $this->path);
		if (file_exists($path)) {
			return realpath($path);
		}

		return $this->path;
	}

	public function getUrl()
	{
		try {
			$publicRoot = \Katu\Config::get('app', 'publicRoot');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$publicRoot = './public/';
		}

		$publicPath = realpath(new \Katu\Utils\File(BASE_DIR, $publicRoot));
		if (preg_match('#^' . $publicPath . '(.*)$#', $this->getPath(), $match)) {
			return new \Katu\Types\TUrl(implode('/', array_map(function ($i) {
				return trim($i, '/');
			}, [
				\Katu\Config::get('app', 'baseUrl'),
				$match[1],
			])));
		}

		return null;
	}

	public function exists()
	{
		return file_exists($this->getPath());
	}

	public function get()
	{
		try {
			return file_get_contents($this);
		} catch (\Exception $e) {
			\Katu\ErrorHandler::log($e);
			return false;
		}
	}

	public function getLines()
	{
		try {
			return file($this);
		} catch (\Exception $e) {
			\Katu\ErrorHandler::log($e);
			return false;
		}
	}

	public function set($data)
	{
		try {
			$this->getDir()->makeDir();
			$this->touch();
			return file_put_contents($this, $data, LOCK_EX);
		} catch (\Exception $e) {
			\Katu\ErrorHandler::log($e);
			return false;
		}
	}

	public function append($data)
	{
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

	public function getSize() {
		clearstatcache();

		return new FileSize(filesize($this));
	}

	public function getMime() {
		clearstatcache();

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->getPath());
		finfo_close($finfo);

		return $mime;
	}

	public function getPathInfo() {
		return pathinfo($this->getPath());
	}

	public function getExtension() {
		$pathinfo = $this->getPathInfo();

		if (isset($pathinfo['extension'])) {
			return $pathinfo['extension'];
		}

		return false;
	}

	public function getDir() {
		return new static(dirname($this));
	}

	public function getBasename() {
		return basename($this);
	}

	public function getFiles($filters = []) {
		$files = [];

		foreach (scandir($this) as $file) {
			$file = new static($this, $file);
			if ($file->isFile()) {
				$files[] = $file;
			}
		}

		if (isset($filters['regexp'])) {
			$files = array_filter($files, function($i) use($filters) {
				return preg_match($filters['regexp'], $i);
			});
		}

		return $files;
	}

	public function getDirs() {
		$files = [];

		foreach (scandir($this) as $file) {
			if ($file != '.' && $file != '..') {
				$file = new static($this, $file);
				if ($file->isDir()) {
					$files[] = $file;
				}
			}
		}

		return $files;
	}

	public function isFile() {
		return $this->getType() == static::TYPE_FILE;
	}

	public function isDir() {
		return $this->getType() == static::TYPE_DIR;
	}

	public function isPhpFile() {
		return $this->isFile() && ($this->getMime() == 'text/x-c++' || $this->getExtension() == 'php');
	}

	public function isWritable() {
		return is_writable($this);
	}

	public function makeDir($mode = 0777, $recursive = true) {
		return @mkdir($this, $mode, $recursive);
	}

	public function touch() {
		$dir = $this->getDir();
		$dir->makeDir();

		return touch($this);
	}

	public function chmod($mode) {
		return chmod($this, $mode);
	}

	public function copy(File $destination) {
		if (!$this->exists()) {
			throw (new \Katu\Exceptions\ErrorException("Source file doesn't exist."))
				->setAbbr('sourceFileUnavailable')
				;
		}

		$destination->touch();
		if (!copy($this, $destination)) {
			throw (new \Katu\Exceptions\ErrorException("Couldn't copy the file."))
				->setAbbr('fileCopyFailed')
				;
		}

		return true;
	}

	public function move(File $destination) {
		$this->copy($destination);
		$this->delete();

		return true;
	}

	public function delete() {
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

	public function getDateTimeModified() {
		return new \Katu\Utils\DateTime('@' . filemtime($this));
	}

	public function eachRecursive($callback) {
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
}
