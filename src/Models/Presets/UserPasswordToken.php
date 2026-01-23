<?php

namespace Katu\Models\Presets;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Users\UserPasswordTokenInterface;

/**
 * @deprecated Use UserPasswordTokenInterface instead. This class is kept for backward compatibility.
 */
abstract class UserPasswordToken extends \Katu\Models\Model implements UserPasswordTokenInterface
{
	const EXPIRES = "1 hour";
	const TABLE = "user_password_tokens";

	public $id;
	public $timeCreated;
	public $timeExpires;
	public $timeUsed;
	public $token;
	public $userId;

	public static function create(UserInterface $user): UserPasswordTokenInterface
	{
		$userPasswordToken = new static;
		$userPasswordToken->setTimeCreated(new Time);
		$userPasswordToken->setTimeExpires(new Time(static::EXPIRES));
		$userPasswordToken->setUser($user);
		$userPasswordToken->setToken(static::generateToken());
		$userPasswordToken->persist();

		return $userPasswordToken;
	}

	public function setTimeCreated(Time $time): UserPasswordToken
	{
		$this->timeCreated = $time;

		return $this;
	}

	public function setTimeExpires(Time $time): UserPasswordToken
	{
		$this->timeExpires = $time;

		return $this;
	}

	public function getTimeExpires(): Time
	{
		return new Time($this->timeExpires);
	}

	public function setTimeUsed(?Time $time): UserPasswordToken
	{
		$this->timeUsed = $time;

		return $this;
	}

	public function getTimeUsed(): ?Time
	{
		return $this->timeUsed ? new Time($this->timeUsed) : null;
	}

	public function setUser(UserInterface $user): UserPasswordTokenInterface
	{
		$this->userId = $user->getId();

		return $this;
	}

	public function getUser(): UserInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}

	public function setToken(string $token): UserPasswordTokenInterface
	{
		$this->token = $token;

		return $this;
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public static function generateToken(): string
	{
		return \Katu\Tools\Random\Generator::getString(static::getColumn("token")->getDescription()->length);
	}

	public function getIsExpired(): bool
	{
		return $this->getTimeExpires()->isInPast();
	}

	public function getIsValid(): bool
	{
		return $this->getTimeExpires()->isInFuture() && !$this->getTimeUsed();
	}

	public function expire(): UserPasswordTokenInterface
	{
		$this->setTimeUsed(new Time);
		$this->persist();

		return $this;
	}
}
