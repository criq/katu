<?php

namespace Katu\Models\Presets;

class UserSetting extends \Katu\Models\Model
{

	const TABLE = 'user_settings';

	public static function getOrCreate($user, $name, $value = null)
	{
		return static::upsert([
			'userId'      => (int)   $user->getId(),
			'name'        => (string)$name,
		], [
			'timeCreated' => (string)new \Katu\Tools\DateTime\DateTime,
		], [
			'value'       => \Katu\Files\Formats\JSON::encodeInline($value),
		]);
	}

	public function getValue()
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->value);
	}
}
