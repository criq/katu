<?php

namespace Katu\Models\Presets;

use Katu\Types\TClass;

class UserPasswordToken extends \Katu\Models\Model
{
	const EXPIRES = '1 hour';
	const TABLE = 'user_password_tokens';

	public static function getUserClass() : TClass
	{
		return new TClass("Katu\Models\Presets\User");
	}

	public static function create(User $user)
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\Calendar\Time,
			'timeExpires' => new \Katu\Tools\Calendar\Time(static::EXPIRES),
			'userId' => $user->getId(),
			'token' => \Katu\Tools\Random\Generator::getString(static::getColumn('token')->getDescription()->length),
		]);
	}

	public function getUser()
	{
		return $this->getUserClass()->getName()::get($this->userId);
	}

	public function isValid()
	{
		return \Katu\Tools\Calendar\Time::get($this->timeExpires)->isInFuture() && !$this->timeUsed;
	}

	public function expire()
	{
		$this->update('timeUsed', \Katu\Tools\Calendar\Time::get()->getDbDateTimeFormat());
		$this->save();

		return true;
	}
}
