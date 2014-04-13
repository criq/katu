<?php

namespace Katu\Utils;

class YAML {

	static function respond($var) {
		$app = \Katu\App::get();

		$app->response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
		$app->response->setBody(self::encode($var));

		return TRUE;
	}

	static function encode($var) {
		return \Spyc::YAMLDump($var);
	}

	static function decode($var) {
		return \Spyc::YAMLLoad($var);
	}

}
