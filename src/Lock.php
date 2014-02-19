<?php

namespace Jabli\Aids;

class Lock {

	static function set($timeout, $filename = '.lock') {
		if (file_exists($filename) && filectime($filename) > time() - $timeout) {
			throw new Exception("Lock exists.");
		}

		touch($filename);

		return TRUE;
	}

	static function remove($filename = '.lock') {
		if (file_exists($filename)) {
			unlink($filename);
		}

		return TRUE;
	}

}
