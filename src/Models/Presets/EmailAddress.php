<?php

namespace Katu\Models\Presets;

abstract class EmailAddress extends \Katu\Models\Model
{
	const TABLE = "email_addresses";

	public static $columnNames = [
		"timeCreated" => "timeCreated",
		"emailAddress" => "emailAddress",
	];

	public static function validate(\Katu\Tools\Validation\Param $param): \Katu\Tools\Validation\Validation
	{
		$result = new \Katu\Tools\Validation\Validation(new \Katu\Tools\Validation\ParamCollection([
			$param,
		]));

		if ($param instanceof \Katu\Tools\Validation\Params\UserInput) {
			try {
				$object = static::getOrCreate($param->getInput());
				$result->setResponse($object);
				$result[] = $param->setOutput($object)->setDisplay($object->getEmailAddress());
			} catch (\Throwable $e) {
				$error = (new \Katu\Errors\Error($e->getMessage()))
					->addParam($param)
					->addVersion("en", "Invalid e-mail address.")
					->addVersion("cs", "Neplatná e-mailová adresa.")
					;

				$result->addError($error);
			}
		}

		return $result;
	}

	public static function create(string $emailAddress): EmailAddress
	{
		if (!static::sanitizeEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			static::$columnNames["timeCreated"] => new \Katu\Tools\Calendar\Time,
			static::$columnNames["emailAddress"] => trim($emailAddress),
		]);
	}

	public static function getOrCreate(string $emailAddress): EmailAddress
	{
		$emailAddress = preg_replace("/\s/", "", $emailAddress);

		if (!static::sanitizeEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::upsert([
			static::$columnNames["emailAddress"] => $emailAddress,
		], [
			static::$columnNames["timeCreated"] => new \Katu\Tools\Calendar\Time,
		]);
	}

	public static function sanitizeEmailAddress(string $value): string
	{
		$value = trim($value);
		if (!$value) {
			throw (new \Katu\Exceptions\InputErrorException("Missing e-mail address."))
				->setAbbr("missingEmailAddress")
				;
		}

		if (!\Katu\Types\TEmailAddress::validateEmailAddress($value)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr("invalidEmailAddress")
				;
		}

		return $value;
	}

	public function getEmailAddress()
	{
		return $this->emailAddress;
	}
}
