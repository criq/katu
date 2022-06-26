<?php

namespace Katu\Storage;

interface AdapterInterface
{
	public function write(string $name, $content);
	public function read(string $name);
}
