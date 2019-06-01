<?php

namespace Katu\Tools\Cron;

class Route {

	public $route;

	public function __construct($route) {
		$this->route = $route;
	}

	public function run() {
		return Url::getFor($this->route)->ping();
	}

}
