<?php

namespace Katu\Models\Presets;

use Katu\Tools\Users\UserLoginTokenInterface;

/**
 * @deprecated Use UserLoginTokenInterface instead. This class is kept for backward compatibility.
 */
abstract class UserLoginToken extends \Katu\Models\Model implements UserLoginTokenInterface
{
	const TABLE = "user_login_tokens";

	public static function create(UserInterface $user, int $timeout = 86400): UserLoginTokenInterface
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"timeExpires" => new \Katu\Tools\Calendar\Time("+ " . $timeout . " seconds"),
			"userId" => $user->getId(),
			"token" => \Katu\Tools\Random\Generator::getString(static::getColumn("token")->getDescription()->length),
		]);
	}

	public function getUser(): UserInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}

	public function setTimeUsed(?\Katu\Tools\Calendar\Time $time): UserLoginToken
	{
		$this->timeUsed = $time;

		return $this;
	}

	public function isValid(): bool
	{
		return (new \Katu\Tools\Calendar\Time($this->timeExpires))->isInFuture() && !(new \Katu\Tools\Calendar\Time($this->timeUsed))->isValid();
	}

	public function expire(): bool
	{
		$this->setTimeUsed(new \Katu\Tools\Calendar\Time);
		$this->persist();

		return true;
	}
}
