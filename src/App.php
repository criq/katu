<?php

namespace Jabli;

use \Slim\Slim;

class App {

	static function getInstance() {
		$app = Slim::getInstance();
		if (!$app) {
			$app = new Slim(Config::get('slim'));
		}

		return $app;
	}

}
