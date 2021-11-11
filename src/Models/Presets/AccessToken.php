<?php

namespace Katu\Models\Presets;

use Katu\Types\TSeconds;
use Sexy\Sexy as SX;

class AccessToken extends \Katu\Models\Model
{
	const EXPIRES = 86400;
	const LENGTH = 128;
	const TABLE = 'access_tokens';

	public static function generateTimeExpires(): \Katu\Tools\DateTime\DateTime
	{
		return new \Katu\Tools\DateTime\DateTime('+ ' . static::EXPIRES . ' seconds');
	}

	public static function create(\Katu\Models\Presets\User $user): AccessToken
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'timeExpires' => static::generateTimeExpires(),
			'userId' => $user->getId(),
			'token' => \Katu\Tools\Random\Generator::getString(static::LENGTH),
		]);
	}

	public static function makeValidForUser(\Katu\Models\Presets\User $user): AccessToken
	{
		$sql = SX::select()
			->from(static::getTable())
			->where(SX::eq(static::getColumn('userId'), (int)$user->getId()))
			->where(SX::cmpGreaterThanOrEqual(static::getColumn('timeExpires'), (new \Katu\Tools\DateTime\DateTime())->getDbDateTimeFormat()))
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
		\Katu\Tools\Cookies\Cookie::set('accessToken', $this->getToken(), $this->getTTL()->getValue());
	}

	public function extend(): AccessToken
	{
		$this->timeExpires = static::generateTimeExpires();
		$this->save();

		return $this;
	}

	public function getTTL(): TSeconds
	{
		return (new \Katu\Tools\DateTime\DateTime($this->timeExpires))->getAge();
	}
}
