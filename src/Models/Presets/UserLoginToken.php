<?php

namespace Katu\Models\Presets;

class UserLoginToken extends \Katu\Models\Model
{
	const TABLE = 'user_login_tokens';

	public static function getUserClass()
	{
		return new \ReflectionClass("\Katu\Models\Presets\User");
	}

	public static function create(User $user, int $timeout = 86400)
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'timeExpires' => new \Katu\Tools\DateTime\DateTime('+ ' . $timeout . ' seconds'),
			'userId' => $user->getId(),
			'token' => \Katu\Tools\Random\Generator::getString(static::getColumn('token')->getProperties()->length),
		]);
	}

	public function getUser()
	{
		return static::getUserClass()->getName()::get($this->userId);
	}

	public function isValid()
	{
		return \Katu\Tools\DateTime\DateTime::get($this->timeExpires)->isInFuture() && !\Katu\Tools\DateTime\DateTime::get($this->timeUsed)->isValid();
	}

	public function expire()
	{
		$this->update('timeUsed', new \Katu\Tools\DateTime\DateTime);
		$this->save();

		return true;
	}
}
