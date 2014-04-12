<?php

namespace Katu\Utils;

class CSV {

	public $path;
	public $writer;
	public $reader;

	public function __construct($path = NULL) {
		if ($path) {
			@touch($path);
			if (!is_writable($path)) {
				throw new Exception("Unable to write into specified file.");
			}

			$this->path = $path;
		} else {
			if (!defined('TMP_PATH')) {
				throw new Exception("Undefined TMP_PATH.");
			}

			$path = TMP_PATH . 'csv_' . Random::getFileName() . '.csv';
			@touch($path);
			if (!is_writable($path)) {
				throw new Exception("Unable to write into a temporary file.");
			}

			$this->path = $path;
		}

		$this->writer = new \EasyCSV\Writer($this->path);
		$this->reader = new \EasyCSV\Reader($this->path);
	}

	public function add() {
		return $this->writer->writeRow(func_get_args());
	}

	public function dump($save_as) {
		header('Content-Type: text/csv; charset=UTF-8');

		return Download::dump($this->path, $save_as, 'inline');
	}

	public function delete() {
		return @unlink($this->path);
	}

}
