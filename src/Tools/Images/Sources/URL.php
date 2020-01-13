<?php

namespace Katu\Tools\Images\Sources;

class URL extends \Katu\Tools\Images\Source {

	public function __construct(\Katu\Types\TURL $input) {
		return parent::__construct($input);
	}

	public function getDir() {
		return pathinfo($this->input->getParts()['path'])['dirname'];
	}

	public function getExtension() {
		try {

			$pathinfo = pathinfo($this->input->getParts()['path']);
			if (isset($pathinfo['extension'])) {
				return $pathinfo['extension'];
			}

			$size = \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function($source) {
				return getimagesize($source->getUri());
			}, $this);

			if (isset($size['mime'])) {
				$extension = (new \Mimey\MimeTypes)->getExtension($size['mime']);
				if ($extension) {
					return $extension;
				}
			}

			throw new \Exception;

		} catch (\Exception $e) {
			return false;
		}
	}

	public function getURI() {
		return (string)$this->input;
	}

	public function getURL() {
		return (string)$this->input;
	}

}