<?php

namespace Katu\Tools\Forms;

use Katu\Types\TSeconds;

class Token
{
	const CODE_LENGTH = 32;
	const ROT_TIMEOUT = 86400 * 2;
	const SPOIL_TIMEOUT = 86400;

	public $code;
	public $timeSpoils;
	public $timeRots;

	public function __construct()
	{
		$this->setCode(static::generateCode());
		$this->setDateTimeSpoils(new \Katu\Tools\DateTime\DateTime("+ " . static::SPOIL_TIMEOUT . " seconds"));
		$this->setDateTimeRots(new \Katu\Tools\DateTime\DateTime("+ " . static::ROT_TIMEOUT . " seconds"));
	}

	public function __toString(): string
	{
		return $this->getCode();
	}

	public function setCode(string $value): Token
	{
		$this->code = $value;

		return $this;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function setDateTimeSpoils(\Katu\Tools\DateTime\DateTime $value): Token
	{
		$this->timeSpoils = $value->format('r');

		return $this;
	}

	public function getDateTimeSpoils(): \Katu\Tools\DateTime\DateTime
	{
		return new \Katu\Tools\DateTime\DateTime($this->timeSpoils);
	}

	public function setDateTimeRots(\Katu\Tools\DateTime\DateTime $value): Token
	{
		$this->timeRots = $value->format('r');

		return $this;
	}

	public function getDateTimeRots(): \Katu\Tools\DateTime\DateTime
	{
		return new \Katu\Tools\DateTime\DateTime($this->timeRots);
	}

	public function getTTL(): TSeconds
	{
		return new TSeconds($this->getDateTimeRots()->getTimestamp() - (new \Katu\Tools\DateTime\DateTime)->getTimestamp());
	}

	public function isAcceptable(): bool
	{
		return $this->isFresh() || $this->isSpoilt();
	}

	public function isFresh(): bool
	{
		return $this->getDateTimeSpoils()->isInFuture();
	}

	public function isSpoilt(): bool
	{
		return $this->getDateTimeSpoils()->isInPast();
	}

	public function isRotten(): bool
	{
		return $this->getDateTimeRots()->isInPast();
	}

	public static function getFreshToken(): Token
	{
		$tokenCollection = TokenCollection::getSessionTokenCollection();
		$freshTokenCollection = $tokenCollection->filterFresh()->sortByTTL();
		if (count($freshTokenCollection)) {
			$token = $freshTokenCollection[0];
		} else {
			$token = new static;
			$tokenCollection->append($token);
			$tokenCollection->saveToSession();
		}

		return $token;
	}

	public static function generateCode(): string
	{
		return strtoupper(\Katu\Tools\Random\Generator::getString(static::CODE_LENGTH));
	}

	public static function validate(string $code): bool
	{
		try {
			return TokenCollection::getSessionTokenCollection()->filterByCode($code)[0]->isAcceptable();
		} catch (\Throwable $e) {
			var_dump($e);die;
			return false;
		}
	}
}
