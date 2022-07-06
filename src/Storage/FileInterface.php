<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;

interface FileInterface
{
	public function getContentType(): string;
	public function getExtension(): ?string;
	public function getFileSize(): TFileSize;
	public function getName(): string;
	public function getURI(): string;
	public function read();
	public function write(string $content): FileInterface;
}
