<?php

namespace Katu\Tools\Cron;

class Path
{
	public $path;

	public function __construct($path)
	{
		$this->path = trim($path, '"\'');
	}

	public function run()
	{
		$url = (new \Katu\Types\TURL(\Katu\Tools\Routing\URL::joinPaths(\Katu\Tools\Routing\URL::getBase(), $this->path)));

		return $url->ping();
	}
}
