<?php

namespace Katu\Tools\Forms;

use \Katu\Tools\DateTime\DateTime;

class Token {

	const TOKEN_TIMEOUT = 86400;
	const TOKEN_LENGTH  = 10;
	const SECRET_LENGTH = 4;

	public $token;
	public $secret;
	public $time;

	public $minDuration;

	public function __construct($params = array()) {
		$this->token  = \Katu\Tools\Random\Generator::getString(self::TOKEN_LENGTH);
		$this->secret = \Katu\Tools\Random\Generator::getNumber(self::SECRET_LENGTH);
		$this->time   = time();

		$this->minDuration = isset($params['minDuration']) ? (int) $params['minDuration'] : 0;
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
