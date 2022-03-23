<?php

namespace Katu\Models\Presets;

use Katu\Types\TSeconds;
use Sexy\Sexy as SX;

class AccessToken extends \Katu\Models\Model
{
	const EXPIRES = 86400;
	const LENGTH = 128;
	const SAFE_TIMEOUT = 3600;
	const TABLE = "access_tokens";

	public static function generateTimeExpires(): \Katu\Tools\DateTime\DateTime
	{
		return new \Katu\Tools\DateTime\DateTime("+ " . static::EXPIRES . " seconds");
	}

	public static function generateToken(): string
	{
		return \Katu\Tools\Random\Generator::getIdString(static::LENGTH);
	}

	public static function create(\Katu\Models\Presets\User $user): AccessToken
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\DateTime\DateTime,
			"timeExpires" => static::generateTimeExpires(),
			"userId" => $user->getId(),
			"token" => static::generateToken(),
		]);
	}

	public static function getOrCreateSafe(\Katu\Models\Presets\User $user): AccessToken
	{
		$sql = SX::select()
			->setGetFoundRows(false)
			->from(static::getTable())
			->where(SX::eq(static::getColumn("userId"), (int)$user->getId()))
			->where(SX::cmpGreaterThanOrEqual(static::getColumn("timeExpires"), new \Katu\Tools\DateTime\DateTime("+ " . static::SAFE_TIMEOUT . " seconds")))
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
		return !(new \Katu\Tools\DateTime\DateTime($this->timeExpires))->isInPast();
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function setCookie()
	{
		\Katu\Tools\Cookies\Cookie::set("accessToken", $this->getToken(), $this->getTTL()->getValue());
	}

	public function getTTL(): TSeconds
	{
		return (new \Katu\Tools\DateTime\DateTime($this->timeExpires))->getAge();
	}
}
