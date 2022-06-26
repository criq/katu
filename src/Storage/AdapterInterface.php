<?php

namespace Katu\Storage;

interface AdapterInterface
{
	public function getSize(string $uri): int;
	public function read(string $name);
	public function write(string $name, $content): Resource;
}
