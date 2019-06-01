<?php

namespace Katu\Models;

use \Katu\Exception;

class EmailAddress extends \Katu\Model {

	const TABLE = 'email_addresses';

	static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddress' => 'emailAddress',
	];

	static function create($emailAddress) {
		if (!static::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			static::$columnNames['timeCreated']  => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			static::$columnNames['emailAddress'] => (string) (trim($emailAddress)),
		));
	}

	static function make($emailAddress) {
		if (!static::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			static::$columnNames['emailAddress'] => $emailAddress,
		), $emailAddress);
	}

	static function checkCrudParams($emailAddress) {
		if (!static::checkEmailAddress($emailAddress)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				->addErrorName('emailAddress')
				;
		}

		return true;
	}

	static function checkEmailAddress($emailAddress) {
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

	static function isValid($emailAddress) {
		return \Katu\Types\TEmailAddress::isValid($emailAddress);
	}

}
