<?php

namespace Katu\Storage;

interface AdapterInterface
{
	public function getContentType(StorageItem $item): string;
	public function getSize(StorageItem $item): int;
	public function getURI(StorageItem $item): string;
	public function read(StorageItem $item);
	public function write(StorageItem $item, $content): StorageItem;
}
