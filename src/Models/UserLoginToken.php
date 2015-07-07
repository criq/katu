<?php

namespace Katu\Models;

class UserLoginToken extends \Katu\Model {

	const TABLE = 'user_login_tokens';

	static function create($user, $timeout = 86400) {
		if (!static::checkCrudParams($user)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'timeExpires' => (string) (\Katu\Utils\DateTime::get('+ ' . $timeout . ' seconds')->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'token'       => (string) (\Katu\Utils\Random::getString(static::getColumn('token')->getProperties()->length)),
		));
	}

	static function checkCrudParams($user) {
		if (!$user || !($user instanceof User)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid user.", 'user');
		}

		return true;
	}

	public function isValid() {
		return \Katu\Utils\DateTime::get($this->timeExpires)->isInFuture() && !\Katu\Utils\DateTime::get($this->timeUsed)->isValid();
	}

	public function expire() {
		$this->update('timeUsed', \Katu\Utils\DateTime::get()->getDbDateTimeFormat());
		$this->save();

		return true;
	}

}
