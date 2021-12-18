<?php

namespace Katu\Tools\Images\Sources;

use Katu\Types\TURL;

class File extends \Katu\Tools\Images\Source
{
	public function __construct(\Katu\Files\File $input)
	{
		return parent::__construct($input);
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
