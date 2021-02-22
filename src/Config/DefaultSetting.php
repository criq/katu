<?php

namespace Katu\Config;

class DefaultSetting
{
	public $description;
	public $isSystem;
	public $name;
	public $value;

	public function __construct($name, $value, $isSystem = true, $description = null)
	{
		$this->name        = $name;
		$this->value       = $value;
		$this->description = $description;
		$this->isSystem    = $isSystem;
	}

	public function make($creator)
	{
		try {
			$setting = \App\Models\Setting::getOneByName($this->name);
		} catch (\Katu\Exceptions\MissingSettingException $e) {
			$setting = \App\Models\Setting::getOrCreate($creator, $this->name, $this->value, $this->isSystem, $this->description);
		}

		return $setting;
	}
}
