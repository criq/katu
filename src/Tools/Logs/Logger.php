<?php

namespace Katu\Tools\Logs;

class Logger extends \Monolog\Logger
{
	const DIR_NAME = 'logs';

	public function __construct($name = 'app')
	{
		parent::__construct(\Katu\Files\File::generatePath($name));

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

	public static function handle($name, string $level, $message)
	{
		$data = [];

		if ($message instanceof \Throwable) {
			$data['class'] = get_class($message);
		}

		if ($message instanceof \Katu\Exceptions\Exception) {
			$data['abbr'] = $message->getAbbr();
			$data['context'] = $message->getContext();
		}

		return (new static($name))->log($level, $message, $data);
	}
}
