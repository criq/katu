<?php

namespace Katu\Tools\Images\Sources;

use Katu\Types\TURL;

class FileModel extends \Katu\Tools\Images\Source
{
	public function __construct(\Katu\Models\Presets\File $input)
	{
		return parent::__construct($input);
	}

	public function getFile(): \Katu\Files\File
	{
		return $this->getInput()->getFile();
	}

	public function getExtension(): string
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
