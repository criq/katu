<?php

namespace Katu\Models\Presets;

use \Sexy\Sexy as SX;

class AccessToken extends \Katu\Models\Model
{
	const EXPIRES = 86400;
	const LENGTH = 128;
	const TABLE = 'access_tokens';

	public static function create(\Katu\Models\Presets\User $user)
	{
		return static::insert([
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'timeExpires' => (string) (\Katu\Tools\DateTime\DateTime::get('+ ' . static::EXPIRES . ' seconds')->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'token'       => (string) (\Katu\Tools\Random\Generator::getString(static::LENGTH)),
		]);
	}

	public static function makeValidForUser(\Katu\Models\Presets\User $user)
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

	public function getRemainingTime()
	{
		return (new \Katu\Tools\DateTime\DateTime($this->timeExpires))->getTimestamp() - (new \Katu\Tools\DateTime\DateTime())->getTimestamp();
	}
}
