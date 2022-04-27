<?php

namespace Katu\Models\Presets;

use Katu\Types\TSeconds;
use Sexy\Sexy as SX;

class AccessToken extends \Katu\Models\Model
{
	const DATABASE = "app";
	const EXPIRES = 86400;
	const LENGTH = 128;
	const SAFE_TIMEOUT = 3600;
	const TABLE = "access_tokens";

	public function getUser(): User
	{
		return \App\App::getUserModelClass()->getName()::get($this->userId);
	}

	public static function generateTimeExpires(): \Katu\Tools\Calendar\Time
	{
		return new \Katu\Tools\Calendar\Time("+ " . static::EXPIRES . " seconds");
	}

	public static function generateToken(): string
	{
		return \Katu\Tools\Random\Generator::getIdString(static::LENGTH);
	}

	public static function create(User $user): AccessToken
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
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
			->where(SX::cmpGreaterThanOrEqual(static::getColumn("timeExpires"), new \Katu\Tools\Calendar\Time("+ " . static::SAFE_TIMEOUT . " seconds")))
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
		return !(new \Katu\Tools\Calendar\Time($this->timeExpires))->isInPast();
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function setCookie(): bool
	{
		return \Katu\Tools\Cookies\Cookie::set("accessToken", $this->getToken(), $this->getTTL()->getValue());
	}

	public function getTTL(): TSeconds
	{
		return (new \Katu\Tools\Calendar\Time($this->timeExpires))->getAge();
	}
}
