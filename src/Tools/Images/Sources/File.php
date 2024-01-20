<?php

namespace Katu\Tools\Images\Sources;

use Katu\Tools\Package\Package;
use Katu\Types\TClass;
use Katu\Types\TURL;

class File extends \Katu\Tools\Images\Source
{
	public function __construct(\Katu\Files\File $input)
	{
		return parent::__construct($input);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"path" => $this->getFile()->getPath(),
		]);
	}

	public static function createFromPackage(Package $package): File
	{
		return new static(new \Katu\Files\File($package->getPayload()["path"]));
	}

	public function getFile(): \Katu\Files\File
	{
		return $this->getInput();
	}

	public function getExtension(): string
	{
		return $this->getInput()->getExtension();
	}

	public function getURI(): string
	{
		return (string)$this->getInput();
	}

	public function getURL(): ?TURL
	{
		return $this->getInput()->getURL();
	}
}
