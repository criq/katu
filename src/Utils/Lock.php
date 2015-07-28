<?php

namespace Katu\Utils;

class Lock {

	public $timeout;
	public $name;

	public function __construct() {
		if (!func_get_arg(0)) {
			throw new \Katu\Exceptions\LockException("Missing lock timeout.");
		}

		$this->timeout = func_get_arg(0);

		if (!func_get_arg(1)) {
			throw new \Katu\Exceptions\LockException("Missing lock name.");
		}

		$this->name = array_slice(func_get_args(), 1);

		if (file_exists($this->getPath()) && filectime($this->getPath()) > (time() - $this->timeout)) {
			throw new \Katu\Exceptions\LockException("Lock exists.");
		}

		FileSystem::touch($this->getPath());

		return true;
	}

	public function getPath() {
		return FileSystem::joinPaths(TMP_PATH, call_user_func(['\Katu\Utils\FileSystem', 'getPathForName'], array_merge(['!locks'], $this->name)));
	}

	public function unlock() {
		if (file_exists($this->getPath())) {
			@unlink($this->getPath());
		}

		return true;
	}

}
