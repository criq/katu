<?php

namespace Katu\Image\Sources;

class File extends \Katu\Image\Source {

	public function __construct(\Katu\Utils\File $input) {
		return parent::__construct($input);
	}

	public function getDir() {
		return $this->input->getDir();
	}

	public function getExtension() {
		return $this->input->getExtension();
	}

	public function getUrl() {
		return $this->input->getUrl();
	}

	public function getFile() {
		return $this->input;
	}

}
