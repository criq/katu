<?php

namespace Katu\Models\Presets;

interface FileInterface
{
	public function getId(): ?string;
	public function getName(): string;
	public function getPath(): \Katu\Files\File;
	public function getFile(): ?\Katu\Files\File;
	public function getSize(): \Katu\Types\TFileSize;
	public function delete(): bool;
}
