<?php

namespace Katu\Models\Presets;

abstract class UserLoginToken extends \Katu\Models\Model
{
	const TABLE = "user_login_tokens";

	public static function create(User $user, int $timeout = 86400)
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"timeExpires" => new \Katu\Tools\Calendar\Time("+ " . $timeout . " seconds"),
			"userId" => $user->getId(),
			"token" => \Katu\Tools\Random\Generator::getString(static::getColumn("token")->getDescription()->length),
		]);
	}

	public function getUser()
	{
		return \App\App::getUserModelClass()->getName()::get($this->userId);
	}

	public function isValid(): bool
	{
		return (new \Katu\Tools\Calendar\Time($this->timeExpires))->isInFuture() && !(new \Katu\Tools\Calendar\Time($this->timeUsed))->isValid();
	}

	public function expire()
	{
		$this->timeUsed = new \Katu\Tools\Calendar\Time;
		$this->save();

		return true;
	}
}
