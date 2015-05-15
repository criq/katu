<?php

namespace Katu\Models;

use \Katu\Exception;

class Setting extends \Katu\Model {

	const TABLE = 'settings';

	static function create($creator, $name, $value, $isSystem, $description = null) {
		if (!static::checkCrudParams($creator, $name, $value, $isSystem)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
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

		return true;
	}

	static function checkName($name, $object = null) {
		if (!trim($name)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing setting name.", 'name');
		}

		$expressions['name'] = trim($name);
		if ($object) {
			$expressions[] = new \Sexy\CmpNotEq(static::getIdColumn(), $object->id);
		}

		if (static::getBy($expressions)->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("Setting already exists.", 'name');
		}

		return true;
	}

	public function setName($name) {
		if (!static::checkName($name, $this)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid setting name.", 'name');
		}

		$this->update('name', trim($name));

		return true;
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

	public function userCanEdit($user) {
		if (!$user) {
			return false;
		}

		return $user->hasPermission('settings.edit');
	}

	public function userCanDelete($user) {
		if (!$user) {
			return false;
		}

		if ($this->isSystem) {
			return false;
		}

		return $user->hasPermission('settings.delete');
	}

}
