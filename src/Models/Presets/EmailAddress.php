<?php

namespace Katu\Models\Presets;

class EmailAddress extends \Katu\Models\Model
{
	const TABLE = 'email_addresses';

	public static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddress' => 'emailAddress',
	];

	public static function create(string $emailAddress): EmailAddress
	{
		if (!static::sanitizeEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			static::$columnNames['timeCreated'] => new \Katu\Tools\DateTime\DateTime,
			static::$columnNames['emailAddress'] => trim($emailAddress),
		]);
	}

	public static function getOrCreate(string $emailAddress): EmailAddress
	{
		$emailAddress = preg_replace("/\s/", "", $emailAddress);

		if (!static::sanitizeEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::upsert([
			static::$columnNames['emailAddress'] => $emailAddress,
		], [
			static::$columnNames['timeCreated'] => new \Katu\Tools\DateTime\DateTime,
		]);
	}

	public static function sanitizeEmailAddress(string $value): string
	{
		$value = trim($value);
		if (!$value) {
			throw (new \Katu\Exceptions\InputErrorException("Missing e-mail address."))
				->setAbbr('missingEmailAddress')
				;
		}

		if (!\Katu\Types\TEmailAddress::validateEmailAddress($value)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				;
		}

		return $value;
	}

	public function getEmailAddress()
	{
		return $this->emailAddress;
	}
}
