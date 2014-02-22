<?php

namespace Jabli\Aids;

class YAML {

	static function encode($var) {
		return \Spyc::YAMLDump($var);
	}

}
