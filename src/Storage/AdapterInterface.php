<?php

namespace Katu\Storage;

interface AdapterInterface
{
	public function getContentType(Item $item): string;
	public function getSize(Item $item): int;
	public function getURI(Item $item): string;
	public function read(Item $item);
	public function write(Item $item, $content): Item;
}
