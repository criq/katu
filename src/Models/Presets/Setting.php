<?php

namespace Katu\Models\Presets;

use \Sexy\Sexy as SX;

class Setting extends \Katu\Models\Model
{
	const TABLE = 'settings';

	public static function getOrCreate(User $creator, string $name, $value, ?bool $isSystem = null, string $description = null) : Setting
	{
		try {
			if (!static::checkName($name)) {
				throw (new \Katu\Exceptions\InputErrorException("Invalid setting name '$name'."))
					->addErrorName('name')
					;
			}
		} catch (\Katu\Exceptions\Exception $e) {
			if ($e->getAbbr() == 'nameInUse') {
				// Nevermind.
			} else {
				throw $e;
			}
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

	public static function checkName(string $name, ?Setting $object = null)
	{
		$name = trim($name);

		if (!$name) {
			throw (new \Katu\Exceptions\InputErrorException("Missing setting name."))
				->addErrorName('name')
				;
		}

		$sql = SX::select()
			->from(static::getTable())
			->where(SX::eq(static::getColumn('name'), $name))
			;

		if ($object) {
			$sql->where(SX::cmpNotEq(static::getIdColumn(), $object->getId()));
		}

		if (static::select($sql)->getResult()->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("Setting name '$name' already used."))
				->setAbbr('nameInUse')
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
