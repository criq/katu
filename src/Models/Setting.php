<?php

namespace Katu\Models;

use \Katu\Exception;

class Setting extends \Katu\Model {

	const TABLE = 'settings';

	static function create() {
		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
		));
	}

	static function getObject($name) {
		return static::getOneBy(array(
			'name' => $name,
		));
	}

	static function get($name) {
		$setting = static::getObject($name);
		if (!$setting) {
			throw new \Katu\Exceptions\MissingSettingException("Missing setting " . $name . ".");
		}

		return \Katu\Utils\JSON::decodeAsArray($setting->value);
	}

}
