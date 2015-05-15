<?php

namespace Katu\Models;

class AccessToken extends \Katu\Model {

	const TABLE = 'access_tokens';

	const EXPIRES = 86400;
	const LENGTH = 128;

	static function create($user) {
		return static::insert([
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'timeExpires' => (string) (\Katu\Utils\DateTime::get('+ ' . static::EXPIRES . ' seconds')->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'accessToken' => (string) (\Katu\Utils\Random::getString(static::LENGTH)),
		]);
	}

	static function makeValidForUser($user) {
		if (!static::checkCrudParams($user)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList([
			'userId' => (int) ($user->getId()),
			new \Sexy\CmpGreaterThanOrEqual(static::getColumn('timeExpires'), (new \Katu\Utils\DateTime())->getDbDateTimeFormat()),
		], $user);
	}

	static function checkCrudParams($user) {
		if (!$user || !($user instanceof User)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid user.", 'user');
		}

		return true;
	}

}
