<?php

namespace Katu\Models;

use \Katu\Exception;

class EmailAddress extends \Katu\Model {

	const TABLE = 'email_addresses';

	static function create($emailAddress) {
		if (!self::checkCrudParams($emailAddress)) {
			throw new \Exception("Invalid arguments.");
		}

		return self::insert(array(
			'timeCreated'  => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'emailAddress' => (string) (trim($emailAddress)),
		));
	}

	static function make($emailAddress) {
		if (!self::checkCrudParams($emailAddress)) {
			throw new \Exception("Invalid arguments.");
		}

		return self::getOneOrCreateWithList(array(
			'emailAddress' => $emailAddress,
		), $emailAddress);
	}

	static function checkCrudParams($emailAddress) {
		if (!self::checkEmailAddress($emailAddress)) {
			throw new \Exception("Invalid e-mail address.");
		}

		return TRUE;
	}

	static function checkEmailAddress($emailAddress) {
		if (!trim($emailAddress)) {
			throw new \Exception("Missing e-mail address.");
		}

		if (!self::isValid($emailAddress)) {
			throw new \Exception("Invalid e-mail address.");
		}

		return TRUE;
	}

	static function isValid($emailAddress) {
		return \Katu\Types\TEmailAddress::isValid($emailAddress);
	}

}
