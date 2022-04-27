<?php

namespace Katu\Models\Presets;

class UserSetting extends \Katu\Models\Model
{
	const DATABASE = "app";
	const TABLE = "user_settings";

	public static function getOrCreate(User $user, string $name, $value = null)
	{
		return static::upsert([
			"userId" => $user->getId(),
			"name" => trim($name),
		], [
			"timeCreated" => (string)new \Katu\Tools\Calendar\Time,
		], [
			"value" => \Katu\Files\Formats\JSON::encodeStandard($value),
		]);
	}

	public function getValue()
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->value);
	}
}
