<?php

namespace Katu\Utils;

class YAML {

	static function respond($var) {
		header('Content-Type: text/plain; charset=UTF-8');

		$app = \Katu\App::get();
		$app->response->setStatus(200);
		$app->response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

		return self::encode($var);
	}

	static function encode($var) {
		return \Spyc::YAMLDump($var);
	}

	static function decode($var) {
		return \Spyc::YAMLLoad($var);
	}

}
