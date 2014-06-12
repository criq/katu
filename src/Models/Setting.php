<?php

namespace Katu\Models;

use \Katu\Exception;

class Setting extends \Katu\Model {

	const TABLE = 'settings';

	static function create($creator, $name, $value, $isSystem, $description = NULL) {
		if (!self::checkCrudParams($creator, $name, $value, $isSystem)) {
			throw new \Katu\Exceptions\InvalidCrudParamsException("Invalid params.");
		}

		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'creatorId'   => (int)    ($creator->id),
			'name'        => (string) (trim($name)),
			'value'       => (string) (trim($value)),
			'isSystem'    => (string) ($isSystem ? '1' : '0'),
			'description' => (string) (trim($description)),
		));
	}

	static function checkCrudParams($creator, $name, $value, $isSystem) {
		if (!$creator || !($creator instanceof \App\Models\Creator)) {
			throw new \Katu\Exceptions\InvalidCrudParamsException("Invalid setting creator.");
		}
		if (!static::checkName($name)) {
			throw new \Katu\Exceptions\InvalidCrudParamsException("Invalid setting name.");
		}

		return TRUE;
	}

	static function checkName($name, $object = NULL) {
		if (!trim($name)) {
			throw new \Katu\Exceptions\InvalidCrudParamsException("Missing setting name.");
		}

		$attr['name'] = trim($name);
		if ($object) {
			$attr['id'] = new \Katu\PDO\Expressions\NotEquals($object->id);
		}

		if (static::getBy($attr)->getTotal()) {
			throw new \Katu\Exceptions\InvalidCrudParamsException("Setting already exists.");
		}

		return TRUE;
	}

	static function getObject($name) {
		return static::getOneBy(array(
			'name' => $name,
		));
	}

	static function getByName($name) {
		$setting = static::getObject($name);
		if (!$setting) {
			throw new \Katu\Exceptions\MissingSettingException("Missing setting " . $name . ".");
		}

		return \Katu\Utils\JSON::decodeAsArray($setting->value);
	}

	static function setupDefaults($creator) {
		foreach (\Katu\Config::get('settings', 'defaults') as $defaultSetting) {
			$defaultSetting->make($creator);
		}
	}

}
