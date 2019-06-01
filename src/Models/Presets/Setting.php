<?php

namespace Katu\Models\Presets;

class Setting extends \Katu\Models\Model {

	const TABLE = 'settings';

	static function create($creator, $name, $value, $isSystem, $description = null) {
		if (!static::checkCrudParams($creator, $name, $value, $isSystem)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'creatorId'   => (int)    ($creator->getId()),
			'name'        => (string) (trim($name)),
			'value'       => (string) (trim($value)),
			'isSystem'    => (string) ($isSystem ? '1' : '0'),
			'description' => (string) (trim($description)),
		));
	}

	static function checkCrudParams($creator, $name, $value, $isSystem) {
		if (!$creator || !($creator instanceof \App\Models\User)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid setting creator."))
				->addErrorName('creator')
				;
		}
		if (!static::checkName($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid setting name."))
				->addErrorName('name')
				;
		}

		return true;
	}

	static function checkName($name, $object = null) {
		if (!trim($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Missing setting name."))
				->addErrorName('name')
				;
		}

		$expressions['name'] = trim($name);
		if ($object) {
			$expressions[] = new \Sexy\CmpNotEq(static::getIdColumn(), $object->getId());
		}

		if (static::getBy($expressions)->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("Setting already exists."))
				->addErrorName('name')
				;
		}

		return true;
	}

	public function setName($name) {
		if (!static::checkName($name, $this)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid setting name."))
				->addErrorName('name')
				;
		}

		$this->update('name', trim($name));

		return true;
	}

	public function getValue() {
		return \Katu\Files\Formats\JSON::decodeAsArray($this->value);
	}

	static function getObject($name) {
		return static::getOneBy(array(
			'name' => trim($name),
		));
	}

	static function getByName($name) {
		$setting = static::getObject($name);
		if (!$setting) {
			throw new \Katu\Exceptions\MissingSettingException("Missing setting " . $name . ".");
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
