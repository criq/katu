<?php

namespace Katu\Models;

class UserPasswordToken extends \Katu\Model
{
	const TABLE = 'user_password_tokens';
	const EXPIRES = '1 hour';

	public static function create($user)
	{
		if (!static::checkCrudParams($user)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			'timeCreated' => (string)new \Katu\Utils\DateTime,
			'timeExpires' => (string)new \Katu\Utils\DateTime(static::EXPIRES),
			'userId'      => (int)   $user->getId(),
			'token'       => (string)\Katu\Utils\Random::getString(static::getColumn('token')->getProperties()->length),
		]);
	}

	public static function checkCrudParams($user)
	{
		if (!$user || !($user instanceof User)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid user."))
				->addErrorName('user')
				;
		}

		return true;
	}

	public function isValid()
	{
		return \Katu\Utils\DateTime::get($this->timeExpires)->isInFuture() && !$this->timeUsed;
	}

	public function expire()
	{
		$this->update('timeUsed', \Katu\Utils\DateTime::get()->getDbDateTimeFormat());
		$this->save();

		return true;
	}
}
