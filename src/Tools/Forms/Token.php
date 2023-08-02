<?php

namespace Katu\Tools\Forms;

use Katu\Tools\Calendar\Seconds;
use Katu\Tools\Calendar\Time;

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
		$this->setDateTimeSpoils(new Time("+ " . static::SPOIL_TIMEOUT . " seconds"));
		$this->setDateTimeRots(new Time("+ " . static::ROT_TIMEOUT . " seconds"));
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

	public function setDateTimeSpoils(Time $value): Token
	{
		$this->timeSpoils = $value->format('r');

		return $this;
	}

	public function getTimeSpoils(): Time
	{
		return new Time($this->timeSpoils);
	}

	public function setDateTimeRots(Time $value): Token
	{
		$this->timeRots = $value->format('r');

		return $this;
	}

	public function getTimeRots(): Time
	{
		return new Time($this->timeRots);
	}

	public function getTTL(): Seconds
	{
		return new Seconds($this->getTimeRots()->getTimestamp() - (new Time)->getTimestamp());
	}

	public function isAcceptable(): bool
	{
		return $this->isFresh() || $this->isSpoilt();
	}

	public function isFresh(): bool
	{
		return $this->getTimeSpoils()->isInFuture();
	}

	public function isSpoilt(): bool
	{
		return $this->getTimeSpoils()->isInPast();
	}

	public function isRotten(): bool
	{
		return $this->getTimeRots()->isInPast();
	}

	public static function getFreshToken(): Token
	{
		$tokenCollection = TokenCollection::createFromSession();
		$freshTokenCollection = $tokenCollection->filterFresh()->sortByTTL();
		if (count($freshTokenCollection)) {
			$token = $freshTokenCollection[0];
		} else {
			$token = new static;
			$tokenCollection->append($token);
			$tokenCollection->persist();
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
			return TokenCollection::createFromSession()->filterByCode($code)[0]->isAcceptable();
		} catch (\Throwable $e) {
			return false;
		}
	}
}
