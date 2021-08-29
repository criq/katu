<?php

namespace Katu\Utils;

class Lock
{
	public $name;
	public $timeout;

	public function __construct($name, $timeout)
	{
		$this->name    = (array) $name;
		$this->timeout = (int)   $timeout;

		if (file_exists($this->getPath()) && filectime($this->getPath()) > (time() - $this->timeout)) {
			throw new \Katu\Exceptions\LockException("Lock exists.");
		}

		FileSystem::touch($this->getPath());

		return true;
	}

	public function getPath()
	{
		return FileSystem::joinPaths(TMP_PATH, call_user_func(['\Katu\Utils\FileSystem', 'getPathForName'], array_merge(['!locks'], $this->name)));
	}

	public static function run($name, $timeout, $callback, $conditions = true)
	{
		@set_time_limit($timeout);

		if ($conditions) {
			$lock = new static($name, $timeout);
		}

		try {
			$args = array_slice(func_get_args(), 3);
			$res = call_user_func_array($callback, $args);
		} catch (\Exception $e) {
			if (isset($lock)) {
				$lock->unlock();
			}

			throw $e;
		}

		if (isset($lock)) {
			$lock->unlock();
		}

		return $res;
	}

	public function unlock()
	{
		if (file_exists($this->getPath())) {
			@unlink($this->getPath());
		}

		return true;
	}
}
