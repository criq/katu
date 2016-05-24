<?php

namespace Katu\Utils;

class FileSize {

	public $size;

	public function __construct($size) {
		$this->size = $size;
	}

	public function __toString() {
		return (string) $this->size;
	}

	public function inKB() {
		return $this->size / 1024;
	}

	public function inMB() {
		return $this->inKB() / 1024;
	}

	public function inGB() {
		return $this->inMB() / 1024;
	}

}
