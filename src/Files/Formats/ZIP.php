<?php

namespace Katu\Files\Formats;

use ZipArchive;

class ZIP
{
	protected $temporaryFile;
	protected $zipArchive;

	public function __construct()
	{
		$file = \Katu\Files\File::createTemporaryWithExtension("zip");
		$file->touch();

		$this->setTemporaryFile($file);

		$this->zipArchive = new ZipArchive;
	}

	public function setTemporaryFile(\Katu\Files\File $temporaryFile): ZIP
	{
		$this->temporaryFile = $temporaryFile;

		return $this;
	}

	public function getTemporaryFile(): \Katu\Files\File
	{
		return $this->temporaryFile;
	}

	public function addFile(\Katu\Files\File $file, ?string $entryName = null): ZIP
	{
		$this->zipArchive->open((string)$this->getTemporaryFile());
		$this->zipArchive->addFile((string)$file, $entryName);
		$this->zipArchive->close();

		return $this;
	}
}
