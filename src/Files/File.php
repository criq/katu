<?php

namespace Katu\Files;

class File
{
	const HASH_ALGO = 'crc32';
	const TYPE_DIR  = 'dir';
	const TYPE_FILE = 'file';

	public $path;

	public function __construct()
	{
		$this->path = static::joinPaths(...func_get_args());
	}

	public function __toString()
	{
		return $this->getPath();
	}

	public static function generatePath($input, ?string $extension = null) : string
	{
		/**************************************************************************
		 * Generate hash.
		 */
		$hash = hash(static::HASH_ALGO, serialize($input));
		// var_dump($hash);

		/**************************************************************************
		 * Make sure input in an array.
		 */
		$output = is_array($input) ? $input : [$input];
		// var_dump($input);

		/**************************************************************************
		 * Flatten array.
		 */
		$output = (new \Katu\Types\TArray($output))->flatten()->getArray();
		// var_dump($output);

		/**************************************************************************
		 * Make sure everything is a string.
		 */
		$output = array_map(function ($i) {
			try {
				return (string)$i;
			} catch (\Throwable $e) {
				return sha1(serialize($i));
			}
		}, $output);
		// var_dump($output);

		/**************************************************************************
		 * Separate into directories.
		 */
		$output = array_map(function ($i) {
			return preg_split('/[\/\\\\&\?=]/', $i);
		}, $output);
		// var_dump($output);

		/**************************************************************************
		 * Flatten array.
		 */
		$output = (new \Katu\Types\TArray($output))->flatten()->getArray();
		// var_dump($output);

		/**************************************************************************
		 * Underscore capital letters.
		 */
		$output = array_map(function ($i) {
			return preg_replace_callback('/\p{Lu}/u', function ($matches) {
				return '_' . mb_strtolower($matches[0]);
			}, $i);
		}, $output);
		// var_dump($output);

		/**************************************************************************
		 * Sanitize dashes and underscores.
		 */
		$output = array_map(function ($i) {
			$i = strtr($i, '-', '_');
			$i = trim($i, '_');

			return $i;
		}, $output);
		// var_dump($output);

		/**************************************************************************
		 * Remove invalid characters.
		 */
		$output = array_map(function ($i) {
			$i = strtr($i, '\\', '/');
			$i = mb_strtolower($i);
			$i = preg_replace('/[^a-z0-9_\/\.]/i', null, $i);
			return $i;
		}, $output);
		// var_dump($output);

		/**************************************************************************
		 * Filter.
		 */
		$output = array_values(array_filter($output));

		try {
			$filename = array_slice($output, -1, 1)[0];
			$pathinfo = pathinfo($filename);

			$hashedFilename = implode('.', array_filter([
				$pathinfo['filename'],
				$hash,
				$extension ?: ($pathinfo['extension'] ?? null),
			]));

			$output = array_merge(array_slice($output, 0, -1), [
				$hashedFilename,
			]);
		} catch (\Throwable $e) {
			$output[] = $hash;
		}

		return implode('/', $output);
	}

	public static function joinPaths()
	{
		return implode('/', array_map(function ($i) {
			$implodedFilename = implode('.', (array)$i);

			return rtrim($implodedFilename, '/');
		}, func_get_args()));
	}

	public static function createTemporaryWithFileName(string $fileName) : File
	{
		return new static(\Katu\App::getTemporaryDir(), 'files', $fileName);
	}

	public static function createTemporaryWithExtension(string $extension) : File
	{
		return new static(\Katu\App::getTemporaryDir(), 'files', [\Katu\Tools\Random\Generator::getFileName(), $extension]);
	}

	public static function createTemporaryFromSrc($src, string $extension) : File
	{
		if ($extension) {
			$file = static::createTemporaryWithExtension($extension);
		} else {
			$file = static::createTemporaryWithFileName(\Katu\Tools\Random\Generator::getFileName());
		}

		$file->set($src);

		return $file;
	}

	public static function createTemporaryFromURL($url, string $extension = null) : File
	{
		$url = new \Katu\Types\TURL($url);

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

		$path = static::joinPaths(\Katu\App::getBaseDir(), $this->path);
		if (file_exists($path)) {
			return realpath($path);
		}

		return $this->path;
	}

	public function getURL()
	{
		try {
			$publicRoot = \Katu\Config\Config::get('app', 'publicRoot');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$publicRoot = './public/';
		}

		$publicPath = realpath(new static(\Katu\App::getBaseDir(), $publicRoot));
		if (preg_match('#^' . $publicPath . '(.*)$#', $this->getPath(), $match)) {
			return new \Katu\Types\TURL(implode('/', array_map(function ($i) {
				return trim($i, '/');
			}, [
				\Katu\Config\Config::get('app', 'baseUrl'),
				$match[1],
			])));
		}

		return null;
	}

	public function exists()
	{
		clearstatcache();

		return file_exists($this->getPath());
	}

	public function get()
	{
		try {
			return file_get_contents($this);
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

	public function getSize()
	{
		clearstatcache();

		return new \Katu\Files\Size(filesize($this));
	}

	public function getMime()
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

		if (isset($pathinfo['extension'])) {
			return $pathinfo['extension'];
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

		if (isset($filters['regexp'])) {
			$files = array_filter($files, function ($i) use ($filters) {
				return preg_match($filters['regexp'], $i);
			});
		}

		return $files;
	}

	public function getDirs()
	{
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
		return $this->isFile() && ($this->getMime() == 'text/x-c++' || $this->getExtension() == 'php');
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

	public function touch()
	{
		$dir = $this->getDir();
		$dir->makeDir();

		return touch($this);
	}

	public function chmod($mode)
	{
		return chmod($this, $mode);
	}

	public function copy(File $destination)
	{
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

	public function getDateTimeModified()
	{
		try {
			return new \Katu\Tools\DateTime\DateTime('@' . filemtime((string)$this));
		} catch (\Throwable $e) {
			return false;
		}
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

	public function getHash($function = 'sha1')
	{
		return hash($function, $this->get());
	}

	public function getHashedURL(?string $algo = 'sha1', ?string $paramName = 'hash')
	{
		if (!$algo) {
			$algo = 'sha1';
		}

		return (new \Katu\Types\TURL($this->getURL()))
			->addQueryParam($paramName, $this->getHash($algo))
			;
	}
}
