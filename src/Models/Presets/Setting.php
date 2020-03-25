<?php

namespace Katu\Models\Presets;

class Setting extends \Katu\Models\Model
{
	const TABLE = 'settings';

	public static function create(User $creator, string $name, $value, bool $isSystem, string $description = null) : Setting
	{
		if (!static::checkName($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid setting name."))
				->addErrorName('name')
				;
		}

		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'creatorId' => $creator->getId(),
			'name' => trim($name),
			'value' => \Katu\Files\Formats\JSON::encodeStandard(trim($value)),
			'isSystem' => $isSystem ? '1' : '0',
			'description' => trim($description) ?: null,
		]);
	}

	public static function checkName(string $name, \Katu\Models\Model $object = null)
	{
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

	public function setName(string $name)
	{
		if (!static::checkName($name, $this)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid setting name."))
				->addErrorName('name')
				;
		}

		$this->update('name', trim($name));

		return true;
	}

	public function getValue()
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->value);
	}

	public static function getObject($name)
	{
		return static::getOneBy([
			'name' => trim($name),
		]);
	}

	public static function getByName($name)
	{
		$setting = static::getObject($name);
		if (!$setting) {
			throw new \Katu\Exceptions\MissingSettingException("Missing setting " . $name . ".");
		}

		return $setting->getValue();
	}

	public static function getAllAsAssoc()
	{
		$settings = array();

		foreach (static::getAll() as $setting) {
			$settings[$setting->name] = $setting->getValue();
		}

		return $settings;
	}

	/****************************************************************************
	 * Permissions.
	 */
	public function userCanEdit($user)
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission('settings.edit');
	}

	public function userCanDelete($user)
	{
		if (!$user) {
			return false;
		}

		if ($this->isSystem) {
			return false;
		}

		return $user->hasPermission('settings.delete');
	}
}
