<?php

namespace Katu\Models\Presets;

abstract class UserSetting extends \Katu\Models\Model
{
	const TABLE = "user_settings";

	public static function getOrCreate(User $user, string $name, $value = null): UserSetting
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
}
