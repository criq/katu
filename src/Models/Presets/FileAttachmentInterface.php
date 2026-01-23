<?php

namespace Katu\Models\Presets;

interface FileAttachmentInterface
{
	public function getId(): ?string;
	public function getFile(): FileInterface;
	public function getObject();
}
