<?php

namespace Katu\Models;

class UserLoginToken extends \Katu\Model {

	const TABLE = 'user_login_tokens';

	static function create($user) {
		if (!static::checkCrudParams($user)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'timeExpires' => (string) (\Katu\Utils\DateTime::get('+ 1 hour')->getDBDatetimeFormat()),
			'userId'      => (int)    ($user->id),
			'token'       => (string) (\Katu\Utils\Random::getString(static::getColumn('token')->getProperties()->length)),
		));
	}

	static function checkCrudParams($user) {
		if (!$user || !($user instanceof User)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid user.", 'user');
		}

		return TRUE;
	}

	public function isValid() {
		return \Katu\Utils\DateTime::get($this->timeExpires)->isInFuture() && !\Katu\Utils\DateTime::get($this->timeUsed)->isValid();
	}

	public function expire() {
		$this->update('timeUsed', \Katu\Utils\DateTime::get()->getDBDatetimeFormat());
		$this->save();

		return TRUE;
	}

}
