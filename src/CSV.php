<?php

namespace Jabli\Aids;

class CSV {

	public $path;

	public function __construct($path = NULL) {
		if ($path) {
			if (!is_writable($path)) {
				throw new Exception("Unable to write into specified file.");
			}

			$this->path = $path;
		} else {
			if (!defined('TMP_PATH')) {
				throw new Exception("Undefined TMP_PATH.");
			}


		}















	}

}
