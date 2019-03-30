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
		$pathinfo = pathinfo($this->input->getParts()['path']);
		if (isset($pathinfo['extension'])) {
			return $pathinfo['extension'];
		}

		try {
			$size = \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function($source) {
				return getimagesize($source->getUri());
			}, $this);
			var_dump($size); die;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function getUri() {
		return (string)$this->input;
	}

	public function getUrl() {
		return (string)$this->input;
	}

}
