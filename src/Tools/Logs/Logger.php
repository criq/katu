<?php

namespace Katu\Tools\Logs;

class Logger extends \Monolog\Logger
{
	const DIR_NAME = 'logs';

	public function __construct(string $channel = 'app')
	{
		parent::__construct($channel);

		$this->pushHandler(new \Monolog\Handler\StreamHandler((string)$this->getFile()));
	}

	public function getDir()
	{
		return new \Katu\Files\File(\Katu\App::getBaseDir(), static::DIR_NAME);
	}

	public function getFile()
	{
		return new \Katu\Files\File($this->getDir(), \Katu\Files\File::generatePath($this->name, 'log'));
	}
}
