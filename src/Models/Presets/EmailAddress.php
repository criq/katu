<?php

namespace Katu\Models\Presets;

use Katu\Errors\Error;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;
use Katu\Types\TString;

abstract class EmailAddress extends \Katu\Models\Model
{
	const TABLE = "email_addresses";

	public $emailAddress;
	public $id;
	public $timeCreated;

	public static $columnNames = [
		"timeCreated" => "timeCreated",
		"emailAddress" => "emailAddress",
	];

	public function beforePersistCallback(): EmailAddress
	{
		$this->emailAddress = static::sanitizeEmailAddress($this->emailAddress);

		return $this;
	}

	public static function validate(Param $emailAddress): Validation
	{
		$validation = new Validation;

		// EmailAddress
		if ($emailAddress->getInput() instanceof static) {
			$output = $emailAddress->getInput();
			$validation->setResponse($output)->addParam($emailAddress->setOutput($output));
		// string
		} else {
			$output = $emailAddress->getInput();
			if (!mb_strlen($output)) {
				$validation->addError((new Error("Chybějící e-mailová adresa."))->addParam($emailAddress));
			} elseif (!\Katu\Types\TEmailAddress::validateEmailAddress($output)) {
				$validation->addError((new Error("Neplatná e-mailová adresa."))->addParam($emailAddress));
			} else {
				$output = static::getOrCreate($output);
				$validation->setResponse($output)->addParam($emailAddress->setOutput($output));
			}
		}

		return $validation;
	}

	public static function getOrCreate(string $string): EmailAddress
	{
		$string = static::sanitizeEmailAddress($string);
		if (!mb_strlen($string)) {
			throw new \Exception("Missing e-mail address.");
		} elseif (!\Katu\Types\TEmailAddress::validateEmailAddress($string)) {
			throw new \Exception("Invalid e-mail address.");
		}

		$emailAddress = static::getOneBy([
			static::$columnNames["emailAddress"] => $string,
		]);

		if (!$emailAddress) {
			$emailAddress = (new static)
				->setTimeCreated(new Time)
				->setEmailAddress($string)
				->persist()
				;
		}

		return $emailAddress;
	}

	public function setTimeCreated(Time $time): EmailAddress
	{
		$this->timeCreated = $time;

		return $this;
	}

	public static function sanitizeEmailAddress(string $string): string
	{
		return (new TString($string))
			->getWithRemovedWhitespace()
			->getWithAccentsRemoved()
			->getLowercase()
			->getString()
			;
	}

	public function setEmailAddress(string $emailAddress): EmailAddress
	{
		$this->emailAddress = static::sanitizeEmailAddress($emailAddress);

		return $this;
	}

	public function getEmailAddress(): string
	{
		return $this->emailAddress;
	}

	public function getTitle(): string
	{
		return $this->getEmailAddress();
	}
}
