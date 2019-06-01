<?php

namespace Katu\PDO;

class Dump {

	public $database;
	public $file;
	public $datetime;

	public function __construct($file, $datetime) {
		$this->database = $file->getDir()->getBasename();
		$this->file     = $file;
		$this->datetime = $datetime;
	}

	static function getAll() {
		$dumps = [];

		$dirs = (new \Katu\Utils\File(BASE_DIR, 'databases'))->getDirs();
		foreach ($dirs as $dir) {
			$files = $dir->getFiles([
				'regexp' => '#[0-9]{12}\.sql\.gz$#',
			]);
			foreach ($files as $file) {
				if (preg_match('#(?<y>[0-9]{4})(?<m>[0-9]{2})(?<d>[0-9]{2})(?<h>[0-9]{2})(?<i>[0-9]{2})(?<s>[0-9]{2})#', $file->getBasename(), $match)) {
					$dumps[] = new static($file, \Katu\Utils\DateTime::createFromFormat('Y-m-d-H-i-s', implode('-', [
						$match['y'],
						$match['m'],
						$match['d'],
						$match['h'],
						$match['i'],
						$match['s'],
					])));
				}
			}
		}

		return $dumps;
	}

	public function delete() {
		return @unlink($this->file);
	}

}

