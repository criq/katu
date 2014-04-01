<?php

namespace App\Models;

use \Jabli\Exception;

class EmailAddress extends \Jabli\Model {

	const TABLE = 'email_addresses';

	public $email_address;

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
		return \Jabli\Types\EmailAddress::isValid($email_address);
	}

}
