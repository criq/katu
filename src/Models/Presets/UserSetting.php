<?php

namespace Katu\Models\Presets;

use Katu\Tools\Users\UserSettingInterface;

/**
 * @deprecated Use UserSettingInterface instead. This class is kept for backward compatibility.
 */
abstract class UserSetting extends \Katu\Models\Model implements UserSettingInterface
{
	const TABLE = "user_settings";

	public static function getOrCreate(UserInterface $user, string $name, $value = null): UserSettingInterface
	{
		return static::upsert([
			"userId" => $user->getId(),
			"name" => trim($name),
		], [
			"timeCreated" => (string)new \Katu\Tools\Calendar\Time,
		], [
			"value" => static::encodeValue($value),
		]);
	}

	public function getValue()
	{
		return static::decodeValue($this->value);
	}

	public static function encodeValue($value)
	{
		return \Katu\Files\Formats\JSON::encodeStandard($value);
	}

	public static function decodeValue($value)
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($value);
	}

	public function getUser(): UserInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}
}
