<?php

namespace Katu\Image\Sources;

class Url extends \Katu\Image\Source {

	public function __construct(\Katu\Types\TUrl $input) {
		return parent::__construct($input);
	}

	public function getDir() {
		return pathinfo($this->input->getParts()['path'])['dirname'];
	}

	public function getExtension() {
		return pathinfo($this->input->getParts()['path'])['extension'];
	}

	public function getUrl() {
		return (string)$this->input;
	}

}
