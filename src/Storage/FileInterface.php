<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;

interface FileInterface
{
	public function getContent();
	public function getContentType(): string;
	public function getExtension(): ?string;
	public function getFileSize(): TFileSize;
	public function getName(): string;
	public function getURI(): string;
	public function setContent(string $content): FileInterface;
}
