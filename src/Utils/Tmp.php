<?php

namespace Katu\Utils;

class Tmp extends File {

	static function getPath($name) {
		return TMP_PATH . FS::getPathForName($name);
	}

	static function debug($var) {
		return static::set(implode('.', ['debug', time(), Random::getFileName(8)]), serialize($var));
	}

}
