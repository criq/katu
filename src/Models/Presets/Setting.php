<?php

namespace Katu\Models\Presets;

use Katu\Tools\Calendar\Time;

abstract class Setting extends \Katu\Models\Model
{
	const TABLE = "settings";

	public $creatorId;
	public $description;
	public $isSystem = 0;
	public $name;
	public $timeCreated;
	public $timeEdited;
	public $value;

	public static function getAllAsAssoc(): array
	{
		$settings = [];
		foreach (static::getAll() as $setting) {
			$settings[$setting->name] = $setting->getValue();
		}

		return $settings;
	}

	public static function getOneByName(string $name): ?Setting
	{
		return static::getOneBy([
			"name" => $name,
		]);
	}

	public static function getOrCreate(?User $creator = null, string $name): Setting
	{
		$setting = static::getOneByName($name);
		if (!$setting) {
			$setting = new static;
			$setting->setTimeCreated(new Time);
			$setting->setTimeEdited(new Time);
			$setting->setCreator($creator);
			$setting->setName($name);
			$setting->persist();
		}

		return $setting;
	}

	public function setTimeCreated(Time $time): Setting
	{
		$this->timeCreated = $time;

		return $this;
	}

	public function setTimeEdited(Time $time): Setting
	{
		$this->timeEdited = $time;

		return $this;
	}

	public function setCreator(?User $creator): Setting
	{
		$this->creatorId = $creator ? $creator->getId() : null;

		return $this;
	}

	public function setName(string $name): Setting
	{
		$this->name = trim($name);

		return $this;
	}

	public function setValue($value): Setting
	{
		$this->value = \Katu\Files\Formats\JSON::encodeStandard($value);
		$this->setTimeEdited(new Time);

		return $this;
	}

	public function getValue()
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->value);
	}

	public function setIsSystem(bool $isSystem): Setting
	{
		$this->isSystem = $isSystem ? 1 : 0;

		return $this;
	}

	public function getIsSystem(): bool
	{
		return (bool)$this->isSystem;
	}

	/****************************************************************************
	 * Permissions.
	 */
	public function userCanEdit(?User $user): bool
	{
		try {
			return $user->hasPermission("settings.edit");
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public function userCanDelete($user)
	{
		try {
			return !$this->getIsSystem() && $user->hasPermission("settings.delete");
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}
}
