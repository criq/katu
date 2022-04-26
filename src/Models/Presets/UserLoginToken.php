<?php

namespace Katu\Models\Presets;

use Katu\Types\TClass;

class UserLoginToken extends \Katu\Models\Model
{
	const TABLE = 'user_login_tokens';

	public static function getUserClass() : TClass
	{
		return new TClass("Katu\Models\Presets\User");
	}

	public static function create(User $user, int $timeout = 86400)
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\Calendar\Time,
			'timeExpires' => new \Katu\Tools\Calendar\Time('+ ' . $timeout . ' seconds'),
			'userId' => $user->getId(),
			'token' => \Katu\Tools\Random\Generator::getString(static::getColumn('token')->getDescription()->length),
		]);
	}

	public function getUser()
	{
		return static::getUserClass()->getName()::get($this->userId);
	}

	public function isValid()
	{
		return \Katu\Tools\Calendar\Time::get($this->timeExpires)->isInFuture() && !\Katu\Tools\Calendar\Time::get($this->timeUsed)->isValid();
	}

	public function expire()
	{
		$this->update('timeUsed', new \Katu\Tools\Calendar\Time);
		$this->save();

		return true;
	}
}
