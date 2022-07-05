<?php

namespace Katu\Utils;

class Tmp extends FileStorage {

	static function getPath($name) {
		$path = FileSystem::joinPaths(TMP_PATH, FileSystem::getPathForName($name));
		try {
			@mkdir(dirname($path), 0777, true);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return $path;
	}

	static function debug($var) {
		return static::set(['!debug', '!' . time(), '!' . Random::getFileName(8)], serialize($var));
	}

	static function save($fileName, $data) {
		if (!$fileName) {
			$fileName = Random::getFileName();
		}

		$path = FileSystem::joinPaths(TMP_PATH, 'files', $fileName);
		try {
			@mkdir(dirname($path), 0777, true);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		file_put_contents($path, $data);

		return $path;
	}

}
