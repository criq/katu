<?php

namespace Katu\Form;

use \Katu\Utils\DateTime;

class Token {

	const TOKEN_TIMEOUT = 3600;
	const TOKEN_LENGTH  = 10;
	const SECRET_LENGTH = 4;

	public $token;
	public $secret;
	public $time;

	public function __construct() {
		$this->token  = \Katu\Utils\Random::getString(self::TOKEN_LENGTH);
		$this->secret = \Katu\Utils\Random::getNumber(self::SECRET_LENGTH);
		$this->time   = time();
	}

	public function isValid() {
		return $this->isInTimeout();
	}

	public function isInTimeout() {
		return DateTime::get($this->time)->isInTimeout(self::TOKEN_TIMEOUT);
	}

	public function getAge() {
		return DateTime::get($this->time)->getAge();
	}

}
