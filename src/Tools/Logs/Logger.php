<?php

namespace Katu\Tools\Logs;

use Katu\Types\TIdentifier;

class Logger extends \Monolog\Logger
{
	const DIR_NAME = 'logs';

	protected $identifier;

	public function __construct(TIdentifier $identifier)
	{
		$this->setIdentifier($identifier);

		parent::__construct(implode('.', $this->getIdentifier()->getSanitizedParts()));

		$this->pushHandler(new \Monolog\Handler\StreamHandler((string)$this->getFile()));
	}

	public function setIdentifier(TIdentifier $identifier): Logger
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function getFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getBaseDir(), static::DIR_NAME, $this->getIdentifier()->getPath('log'));
	}

	public function log($level, $message, array $context = []): void
	{
		if ($message instanceof \Throwable) {
			$context['class'] = get_class($message);
		}

		if ($message instanceof \Katu\Exceptions\Exception) {
			$context['abbr'] = $message->getAbbr();
			$context['context'] = $message->getContext();
		}

		// $this->getFile()->chmod(0777);
		parent::log($level, $message, $context);
	}

	public function error($message, array $context = []): void
	{
		$this->log('error', $message, $context);
	}
}
