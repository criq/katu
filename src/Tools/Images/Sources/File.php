<?php

namespace Katu\Tools\Images\Sources;

class File extends \Katu\Tools\Images\Source {

	public function __construct(\Katu\Models\Presets\File $input) {
		return parent::__construct($input);
	}

	public function getDir() {
		return $this->input->getDir();
	}

	public function getExtension() {
		return $this->input->getExtension();
	}

	public function getURI() {
		return (string)$this->input->getFile();
	}

	public function getURL() {
		return false;
	}

	public function getFile() {
		return $this->input;
	}

}
