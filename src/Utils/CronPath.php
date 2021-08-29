<?php

namespace Katu\Utils;

class CronPath
{
	public $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function run()
	{
		return (new \Katu\Types\TUrl(Url::joinPaths(Url::getBase(), $this->path)))->ping();
	}
}
