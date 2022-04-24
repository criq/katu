<?php

namespace Katu\Tools\Images\Sources;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;

class URL extends \Katu\Tools\Images\Source
{
	public function __construct(\Katu\Types\TURL $input)
	{
		return parent::__construct($input);
	}

	public function getFile(): \Katu\Files\File
	{
		return pathinfo($this->getInput()->getParts()['path'])['dirname'];
	}

	public function getExtension(): string
	{
		try {
			$pathinfo = pathinfo($this->getInput()->getParts()['path']);
			if (isset($pathinfo['extension'])) {
				return $pathinfo['extension'];
			}

			$size = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout('1 year'), function ($source) {
				return getimagesize($source->getUri());
			}, $this);

			if (isset($size['mime'])) {
				$extension = (new \Mimey\MimeTypes)->getExtension($size['mime']);
				if ($extension) {
					return $extension;
				}
			}

			throw new \Exception;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getURI(): string
	{
		return (string)$this->getInput();
	}

	public function getURL(): ?TURL
	{
		return $this->getInput();
	}
}
