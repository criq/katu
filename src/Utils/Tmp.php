<?php

namespace Katu\Utils;

class Tmp extends FileStorage {

	static function getPath($name) {
		return FileSystem::joinPaths(TMP_PATH, FileSystem::getPathForName($name));
	}

	static function debug($var) {
		return static::set(implode('.', ['debug', time(), Random::getFileName(8)]), serialize($var));
	}

}
