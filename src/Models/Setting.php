<?php

namespace Katu\Models;

use \Katu\Exception;

class Setting extends \Katu\Model {

	const TABLE = 'settings';

	static function create($creator, $name, $value, $isSystem, $description = NULL) {
		if (!self::checkCrudParams($creator, $name, $value, $isSystem)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
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
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid setting creator.", 'creator');
		}
		if (!static::checkName($name)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid setting name.", 'name');
		}

		return TRUE;
	}

	static function checkName($name, $object = NULL) {
		if (!trim($name)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing setting name.", 'name');
		}

		$attr['name'] = trim($name);
		if ($object) {
			$attr['id'] = new \Katu\Pdo\Expressions\NotEquals($object->id);
		}

		if (static::getBy($attr)->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("Setting already exists.", 'name');
		}

		return TRUE;
	}

	public function setName($name) {
		if (!static::checkName($name, $this)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid setting name.", 'name');
		}

		$this->update('name', trim($name));

		return TRUE;
	}

	public function getValue() {
		return \Katu\Utils\JSON::decodeAsArray($this->value);
	}

	static function getObject($name) {
		return static::getOneBy(array(
			'name' => trim($name),
		));
	}

	static function getByName($name) {
		$setting = static::getObject($name);
		if (!$setting) {
			throw new \Katu\Exceptions\MissingSettingException("Missing setting " . $name . ".", 'name');
		}

		return $setting->getValue();
	}

	static function getAllAsAssoc() {
		$settings = array();

		foreach (static::getAll() as $setting) {
			$settings[$setting->name] = $setting->getValue();
		}

		return $settings;
	}

	static function setupDefaults($creator) {
		foreach (\Katu\Config::get('settings', 'defaults') as $defaultSetting) {
			$defaultSetting->make($creator);
		}
	}

}
