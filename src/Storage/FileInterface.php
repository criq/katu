<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;

interface FileInterface
{
	public function getContentType(): string;
	public function getFileSize(): TFileSize;
	public function getName(): string;
	public function getURI(): string;
}
