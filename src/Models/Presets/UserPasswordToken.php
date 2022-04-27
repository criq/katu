<?php

namespace Katu\Models\Presets;

abstract class UserPasswordToken extends \Katu\Models\Model
{
	const EXPIRES = "1 hour";
	const TABLE = "user_password_tokens";

	public static function create(User $user)
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"timeExpires" => new \Katu\Tools\Calendar\Time(static::EXPIRES),
			"userId" => $user->getId(),
			"token" => \Katu\Tools\Random\Generator::getString(static::getColumn("token")->getDescription()->length),
		]);
	}

	public function getUser()
	{
		return \App\App::getUserModelClass()->getName()::get($this->userId);
	}

	public function isValid()
	{
		return (new \Katu\Tools\Calendar\Time($this->timeExpires))->isInFuture() && !$this->timeUsed;
	}

	public function expire()
	{
		$this->timeUsed = (new \Katu\Tools\Calendar\Time)->getDbDateTimeFormat();
		$this->save();

		return true;
	}
}
