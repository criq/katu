<?php

namespace Jabli\Utils;

class YAML {

	static function respond($var) {
		$app = \Jabli\FW::getApp();
		$app->response->setStatus(200);
		$app->response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

		return self::encode($var);
	}

	static function encode($var) {
		return \Spyc::YAMLDump($var);
	}

}
