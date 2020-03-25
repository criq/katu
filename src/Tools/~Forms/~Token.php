<?php

// namespace Katu\Tools\Forms;

// use \Katu\Tools\DateTime\DateTime;

// class Token
// {
// 	const SECRET_LENGTH = 4;
// 	const TOKEN_LENGTH = 10;
// 	const TOKEN_TIMEOUT = 86400;

// 	public $minDuration;
// 	public $secret;
// 	public $time;
// 	public $token;

// 	public function __construct($params = [])
// 	{
// 		$this->token = \Katu\Tools\Random\Generator::getString(static::TOKEN_LENGTH);
// 		$this->secret = \Katu\Tools\Random\Generator::getNumber(static::SECRET_LENGTH);
// 		$this->time = time();
// 		$this->minDuration = (int)$params['minDuration'] ?? 0;
// 	}

// 	public function isValid()
// 	{
// 		return $this->isInTimeout();
// 	}

// 	public function isInTimeout()
// 	{
// 		return DateTime::get($this->time)->isInTimeout(static::TOKEN_TIMEOUT);
// 	}

// 	public function getAge()
// 	{
// 		return DateTime::get($this->time)->getAge();
// 	}
// }
