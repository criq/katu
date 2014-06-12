<?php

namespace Katu\Utils;

class DefaultSetting {

	public $name;
	public $value;
	public $description;
	public $isSystem;

	public function __construct($name, $value, $isSystem = TRUE, $description = NULL) {
		$this->name = $name;
		$this->value = $value;
		$this->description = $description;
		$this->isSystem = $isSystem;
	}

	public function make($creator) {
		try {
			$setting = \App\Models\Setting::getByName($this->name);
		} catch (\Katu\Exceptions\MissingSettingException $e) {
			$setting = \App\Models\Setting::create($creator, $this->name, $this->value, $this->isSystem, $this->description);
		}

		return $setting;
	}

}
