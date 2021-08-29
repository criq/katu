<?php

namespace Katu\Models;

class EmailAddress extends \Katu\Model
{
	const TABLE = 'email_addresses';

	public static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddress' => 'emailAddress',
	];

	public static function create($emailAddress)
	{
		if (!static::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			static::$columnNames['timeCreated']  => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			static::$columnNames['emailAddress'] => (string) (trim($emailAddress)),
		]);
	}

	public static function make($emailAddress)
	{
		if (!static::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::upsert([
			static::$columnNames['emailAddress'] => $emailAddress,
		], [
			static::$columnNames['timeCreated'] => new \Katu\Utils\DateTime,
		]);
	}

	public static function checkCrudParams($emailAddress)
	{
		if (!static::checkEmailAddress($emailAddress)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				->addErrorName('emailAddress')
				;
		}

		return true;
	}

	public static function checkEmailAddress($emailAddress)
	{
		if (!trim($emailAddress)) {
			throw (new \Katu\Exceptions\InputErrorException("Missing e-mail address."))
				->setAbbr('missingEmailAddress')
				->addErrorName('emailAddress')
				->addTranslation('cs', "Chybějící e-mailová adresa.")
				;
		}

		if (!static::isValid($emailAddress)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				->addErrorName('emailAddress')
				->addTranslation('cs', "Neplatná e-mailová adresa.")
				;
		}

		return true;
	}

	public static function isValid($emailAddress)
	{
		return \Katu\Types\TEmailAddress::isValid($emailAddress);
	}
}
