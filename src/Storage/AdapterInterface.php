<?php

namespace Katu\Storage;

interface AdapterInterface
{
	public function getSize(Item $item): int;
	public function read(Item $item);
	public function write(Item $item, $content): Item;
}
