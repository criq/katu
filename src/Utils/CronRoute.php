<?php

namespace Katu\Utils;

class CronRoute {

	public $route;

	public function __construct($route) {
		$this->route = $route;
	}

	public function run() {
		return Url::getFor($this->route)->ping();
	}

}
