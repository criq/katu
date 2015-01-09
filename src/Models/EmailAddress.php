<?php

namespace Katu\Models;

use \Katu\Exception;

class EmailAddress extends \Katu\Model {

	const TABLE = 'email_addresses';

	static function create($emailAddress) {
		if (!static::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated'  => (string) (\Katu\Utils\DateTime::get()->getDbDatetimeFormat()),
			'emailAddress' => (string) (trim($emailAddress)),
		));
	}

	static function make($emailAddress) {
		if (!static::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			'emailAddress' => $emailAddress,
		), $emailAddress);
	}

	static function checkCrudParams($emailAddress) {
		if (!static::checkEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		return true;
	}

	static function checkEmailAddress($emailAddress) {
		if (!trim($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing e-mail address.", 'emailAddress');
		}

		if (!static::isValid($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		return true;
	}

	static function isValid($emailAddress) {
		return \Katu\Types\TEmailAddress::isValid($emailAddress);
	}

}
