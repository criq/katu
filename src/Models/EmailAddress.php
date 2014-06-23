<?php

namespace Katu\Models;

use \Katu\Exception;

class EmailAddress extends \Katu\Model {

	const TABLE = 'email_addresses';

	static function create($emailAddress) {
		if (!self::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return self::insert(array(
			'timeCreated'  => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'emailAddress' => (string) (trim($emailAddress)),
		));
	}

	static function make($emailAddress) {
		if (!self::checkCrudParams($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return self::getOneOrCreateWithList(array(
			'emailAddress' => $emailAddress,
		), $emailAddress);
	}

	static function checkCrudParams($emailAddress) {
		if (!self::checkEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		return TRUE;
	}

	static function checkEmailAddress($emailAddress) {
		if (!trim($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing e-mail address.", 'emailAddress');
		}

		if (!self::isValid($emailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		return TRUE;
	}

	static function isValid($emailAddress) {
		return \Katu\Types\TEmailAddress::isValid($emailAddress);
	}

}
