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

		return \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function($source) {

			try {
				$size = getimagesize($source->getUri());
				if (isset($size['mime'])) {
					switch ($size['mime']) {
						case 'image/jpeg' : return "jpg"; break;
						case 'image/png'  : return "png"; break;
						case 'image/gif'  : return "jpg"; break;
						default: return false; break;
					}
				}
				return new \Katu\Types\TImageSize($size[0], $size[1]);
			} catch (\Exception $e) {
				return false;
			}

		}, $this);
	}

	public function getUri() {
		return (string)$this->input;
	}

	public function getUrl() {
		return (string)$this->input;
	}

}
