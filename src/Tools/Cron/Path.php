<?php

namespace Katu\Tools\Cron;

class Path
{
	public $path;

	public function __construct(string $path)
	{
		$this->setPath($path);
	}

	public function setPath(string $path): Path
	{
		$this->path = trim($path, "\"'");

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function run()
	{
		$url = (new \Katu\Types\TURL(\Katu\Tools\Routing\URL::joinPaths(\Katu\Tools\Routing\URL::getBase(), $this->getPath())));

		return $url->ping();
	}
}
