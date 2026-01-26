<?php

namespace Katu\Models\Presets;

/**
 * @deprecated
 */
interface FileAttachmentInterface
{
	public function getId(): ?string;
	public function getFile(): FileInterface;
	public function getObject();
}
