<?php

namespace Katu\Models;

use \Sexy\Sexy as SX;

class AccessToken extends \Katu\Model {

	const TABLE = 'access_tokens';

	const EXPIRES = 86400;
	const LENGTH = 128;

	static function create($user) {
		return static::insert([
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'timeExpires' => (string) (\Katu\Utils\DateTime::get('+ ' . static::EXPIRES . ' seconds')->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'token'       => (string) (\Katu\Utils\Random::getString(static::LENGTH)),
		]);
	}

	static function makeValidForUser($user) {
		if (!static::checkCrudParams($user)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		$sql = SX::select()
			->from(static::getTable())
			->where(SX::eq(static::getColumn('userId'), (int)$user->getId()))
			->where(SX::cmpGreaterThanOrEqual(static::getColumn('timeExpires'), (new \Katu\Utils\DateTime())->getDbDateTimeFormat()))
			;

		$object = static::getOneBySql($sql);
		if (!$object) {
			$object = static::create($user);
		}

		return $object;
	}

	static function checkCrudParams($user) {
		if (!$user || !($user instanceof User)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid user."))
				->addErrorName('user')
				;
		}

		return true;
	}

	public function getRemainingTime() {
		return (new \Katu\Utils\DateTime($this->timeExpires))->getTimestamp() - (new \Katu\Utils\DateTime())->getTimestamp();
	}

}
