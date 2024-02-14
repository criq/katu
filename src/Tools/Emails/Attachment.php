<?php

namespace Katu\Tools\Emails;

class Attachment
{
	protected $file;
	protected $name;
	protected $cid;

	public function __construct(\Katu\Files\File $file, ?string $name = null, ?string $cid = null)
	{
		$this->setFile($file);
		$this->setName($name);
		$this->setCID($cid);
	}

	public function setFile(\Katu\Files\File $file): Attachment
	{
		$this->file = $file;

		return $this;
	}

	public function getFile(): \Katu\Files\File
	{
		return $this->file;
	}

	public function setName(?string $name): Attachment
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setCID(): ?string
	{
		return $this->cid;
	}
}
