<?php

namespace Katu\Models\Presets;

class UserPasswordToken extends \Katu\Model {

	const TABLE = 'user_password_tokens';

	const EXPIRES = '1 hour';

	static function create($user) {
		if (!static::checkCrudParams($user)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'timeExpires' => (string) (\Katu\Tools\DateTime\DateTime::get(static::EXPIRES)->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'token'       => (string) (\Katu\Utils\Random::getString(static::getColumn('token')->getProperties()->length)),
		));
	}

	static function checkCrudParams($user) {
		if (!$user || !($user instanceof User)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid user."))
				->addErrorName('user')
				;
		}

		return true;
	}

	public function isValid() {
		return \Katu\Tools\DateTime\DateTime::get($this->timeExpires)->isInFuture() && !$this->timeUsed;
	}

	public function expire() {
		$this->update('timeUsed', \Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat());
		$this->save();

		return true;
	}

}
