<?php

namespace Katu\Image\Sources;

class File extends \Katu\Image\Source {

	public function __construct(\Katu\Models\File $input) {
		return parent::__construct($input);
	}

	public function getDir() {
		return $this->input->getDir();
	}

	public function getExtension() {
		return $this->input->getExtension();
	}

	public function getUri() {
		return (string)$this->input->getFile();
	}

	public function geTURL() {
		return false;
	}

	public function getFile() {
		return $this->input;
	}

}
