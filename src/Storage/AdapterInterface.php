<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;

interface AdapterInterface
{
	public function delete(Entity $item): bool;
	public function getContentType(Entity $item): string;
	public function getFileSize(Entity $item): TFileSize;
	public function getURI(Entity $item): string;
	public function listEntities(Storage $storage): iterable;
	public function read(Entity $item);
	public function write(Entity $item, $content): Entity;
}
