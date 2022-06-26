<?php

namespace Katu\Storage;

interface AdapterInterface
{
	public function write(string $name, $content): Resource;
	public function read(string $name);
}
