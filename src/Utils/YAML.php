<?php

namespace Jabli\Utils;

class YAML {

	static function respond($var) {
		header('Content-Type: text/plain; charset=UTF-8');

		return self::encode($var);
	}

	static function encode($var) {
		return \Spyc::YAMLDump($var);
	}

}
