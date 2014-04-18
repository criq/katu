<?php

namespace Katu\Models;

use \Katu\Exception;

class EmailAddress extends \Katu\Model {

	const TABLE = 'email_addresses';

	static function create($email_address) {
		if (!self::checkCRUDParams($email_address)) {
			throw new Exception("Invalid params.");
		}

		return self::insert(array(
			'email_address' => (string) (trim($email_address)),
		));
	}

	static function make($email_address) {
		if (!self::checkCRUDParams($email_address)) {
			throw new Exception("Invalid params.");
		}

		return self::getOrCreate(array('email_address' => $email_address), $email_address);
	}

	static function checkCRUDParams($email_address) {
		if (!self::checkEmailAddress($email_address)) {
			throw new Exception("Invalid e-mail address.");
		}

		return TRUE;
	}

	static function checkEmailAddress($email_address) {
		if (!trim($email_address)) {
			throw new Exception("Missing e-mail address.");
		}

		if (!self::isValid($email_address)) {
			throw new Exception("Invalid e-mail address.");
		}

		return TRUE;
	}

	static function isValid($email_address) {
		return \Katu\Types\TEmailAddress::isValid($email_address);
	}

}
