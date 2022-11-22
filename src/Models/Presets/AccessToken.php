<?php

namespace Katu\Models\Presets;

use Katu\Tools\Calendar\Seconds;
use Katu\Tools\Calendar\Time;
use Sexy\Sexy as SX;

abstract class AccessToken extends \Katu\Models\Model
{
	const EXPIRES = 86400 * 7;
	const LENGTH = 128;
	const SAFE_TIMEOUT = 3600;
	const TABLE = "access_tokens";

	public function setUser(User $user): AccessToken
	{
		$this->userId = $user->getId();

		return $this;
	}

	public function getUser(): User
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}

	public static function generateTimeExpires(): Time
	{
		$expires = static::EXPIRES;

		return new Time("+ {$expires} seconds");
	}

	public function setTimeExpires(Time $time): AccessToken
	{
		$this->timeExpires = $time;

		return $this;
	}

	public function getTimeExpires(): Time
	{
		return new Time($this->timeExpires);
	}

	public static function generateToken(): string
	{
		return \Katu\Tools\Random\Generator::getIdString(static::LENGTH);
	}

	public static function create(User $user): AccessToken
	{
		return static::insert([
			"timeCreated" => new Time,
			"timeExpires" => static::generateTimeExpires(),
			"userId" => $user->getId(),
			"token" => static::generateToken(),
		]);
	}

	public static function getOrCreateSafe(User $user): AccessToken
	{
		$sql = SX::select()
			->setGetFoundRows(false)
			->from(static::getTable())
			->where(SX::eq(static::getColumn("userId"), (int)$user->getId()))
			->where(SX::cmpGreaterThanOrEqual(static::getColumn("timeExpires"), new Time("+ " . static::SAFE_TIMEOUT . " seconds")))
			->orderBy(SX::orderBy(static::getColumn("timeExpires"), SX::kw("desc")))
			->setPage(SX::page(1, 1))
			;

		$object = static::getOneBySql($sql);
		if (!$object) {
			$object = static::create($user);
		}

		return $object;
	}

	public function getIsValid(): bool
	{
		return !(new Time($this->timeExpires))->isInPast();
	}

	public function setToken(string $token): AccessToken
	{
		$this->token = $token;

		return $this;
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function setCookie(): bool
	{
		return \Katu\Tools\Cookies\Cookie::set("accessToken", $this->getToken(), $this->getTTL()->getValue());
	}

	public function getTTL(): Seconds
	{
		return (new Time($this->timeExpires))->getAge();
	}
}
