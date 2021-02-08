<?php

namespace Katu\Models\Presets;

class EmailAddress extends \Katu\Models\Model
{
	const TABLE = 'email_addresses';

	public static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddress' => 'emailAddress',
	];

	public static function create(string $emailAddress) : EmailAddress
	{
		if (!static::checkEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			static::$columnNames['timeCreated']  => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			static::$columnNames['emailAddress'] => (string) (trim($emailAddress)),
		]);
	}

	public static function make(string $emailAddress) : EmailAddress
	{
		$emailAddress = preg_replace('/\s/', null, $emailAddress);

		if (!static::checkEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			static::$columnNames['emailAddress'] => $emailAddress,
		), $emailAddress);
	}

	public static function checkEmailAddress(string $emailAddress) : bool
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

	public static function isValid($emailAddress) : bool
	{
		return \Katu\Types\TEmailAddress::validateEmailAddress($emailAddress);
	}

	public function getEmailAddress()
	{
		return $this->emailAddress;
	}
}
