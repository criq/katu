<?php

namespace Katu\Tools\Images\Sources;

use Katu\Tools\Images\Source;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;
use Katu\Types\TURL;

class FileModel extends \Katu\Tools\Images\Source
{
	public function __construct(\Katu\Models\Presets\File $input)
	{
		return parent::__construct($input);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"fileId" => $this->getInput()->getId(),
		]);
	}

	public static function createFromPackage(Package $package): Source
	{
		$fileClass = \App\App::getContainer()->get(\Katu\Models\Presets\File::class);

		return new static($fileClass::get($package->getPayload()["fileId"]));
	}

	public function getLocalFile(): ?\Katu\Files\File
	{
		return $this->getInput()->getFile();
	}

	public function getExtension(): ?string
	{
		return $this->getInput()->getExtension();
	}

	public function getURI(): string
	{
		return (string)$this->getInput()->getFile();
	}

	public function getURL(): ?TURL
	{
		return null;
	}
}
