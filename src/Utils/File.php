<?php

namespace Katu\Utils;

class File {

	public $path;

	const TYPE_FILE = 'file';
	const TYPE_DIR  = 'dir';

	public function __construct() {
		$this->path = call_user_func_array(['\Katu\Utils\FileSystem', 'joinPaths'], func_get_args());

		return $this;
	}

	public function __toString() {
		return $this->getPath();
	}

	public function getPath() {
		if (file_exists($this->path)) {
			return $this->path;
		}

		$path = FileSystem::joinPaths(BASE_DIR, $this->path);
		if (file_exists($path)) {
			return realpath($path);
		}

		return $this->path;
	}

	public function exists() {
		return file_exists($this->getPath());
	}

	public function get() {
		try {

			return file_get_contents($this);

		} catch (\Exception $e) {

			\Katu\ErrorHandler::log($e);

			return false;

		}
	}

	public function set($data) {
		try {

			$this->getDir()->makeDir();

			return file_put_contents($this, $data, LOCK_EX);

		} catch (\Exception $e) {

			\Katu\ErrorHandler::log($e);

			return false;

		}
	}

	public function getType() {
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

	public function makeDir($permissions = 0777, $recursive = true) {
		@mkdir($this, $permissions, $recursive);

		return $this;
	}

	public function touch() {
		$dir = $this->getDir();
		$dir->makeDir();

		return touch($this);
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

	public function eachRecursive($callback) {
		$iterator = new \RecursiveDirectoryIterator($this, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file) {
			call_user_func_array($callback, [new static((string) $file)]);
		}
	}

	public function includeOnce() {
		return include_once $this;
	}

	public function includeAllPhpFiles() {
		return $this->eachRecursive(function($i) {
			if ($i->isPhpFile()) {
				$i->includeOnce();
			}
		});
	}

}
