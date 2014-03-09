<?php

namespace Jabli\Utils;

class Lock {

	public $timeout;
	public $path;

	public function __construct() {
		if (!func_get_arg(0)) {
			throw new Exception("Misisng lock timeout.");
		}

		$this->timeout = func_get_arg(0);

		if (!func_get_arg(1)) {
			throw new Exception("Misisng lock name.");
		}

		if (!defined('TMP_PATH')) {
			throw new Exception("Undefined TMP_PATH.");
		}

		$this->path = rtrim(TMP_PATH, '/') . '/.lock__' . implode('__', array_slice(func_get_args(), 1));

		if (file_exists($this->path) && filectime($this->path) > (time() - $this->timeout)) {
			throw new Exception("Lock exists.");
		}

		touch($this->path);

		return TRUE;
	}

	public function unlock() {
		if (file_exists($this->path)) {
			unlink($this->path);
		}

		return TRUE;
	}

}
