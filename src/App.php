<?php

namespace Jabli;

use \Slim\Slim;

class App {

	static function getApp() {
		$app = Slim::getInstance();
		if (!$app) {
			$app = new Slim(Config::get('slim'));
		}

		return $app;
	}

	static function getDB() {
		return DB\Connection::getInstance();
	}

}
