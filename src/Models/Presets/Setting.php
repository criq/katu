<?php

namespace Katu\Models\Presets;

class Setting extends \Katu\Models\Model
{
	const TABLE = 'settings';

	public static function make(User $creator, string $name, $value, ?bool $isSystem = null, string $description = null) : Setting
	{
		if (!static::checkName($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid setting name."))
				->addErrorName('name')
				;
		}

		return static::upsert([
			'name' => trim($name),
		], [
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'creatorId' => $creator->getId(),
		], [
			'timeEdited' => new \Katu\Tools\DateTime\DateTime,
			'value' => \Katu\Files\Formats\JSON::encodeStandard($value),
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
