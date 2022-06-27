<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;

interface AdapterInterface
{
	public function delete(StorageItem $item): bool;
	public function getContentType(StorageItem $item): string;
	public function getFileSize(StorageItem $item): TFileSize;
	public function getURI(StorageItem $item): string;
	public function read(StorageItem $item);
	public function write(StorageItem $item, $content): StorageItem;
}
