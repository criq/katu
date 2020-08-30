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

	public function log($level, $message, array $context = []) : void
	{
		if ($message instanceof \Throwable) {
			$context['class'] = get_class($message);
		}

		if ($message instanceof \Katu\Exceptions\Exception) {
			$context['abbr'] = $message->getAbbr();
			$context['context'] = $message->getContext();
		}

		parent::log($level, $message, $context);

		$this->getFile()->chmod(0777);
	}

	public function error($message, array $context = []) : void
	{
		$this->log('error', $message, $context);
	}
}
