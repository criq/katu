<?php

namespace Katu\Utils;

class CSV {

	public $path;
	public $writer;
	public $reader;

	public function __construct($path = NULL, $options = array()) {
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

		if (isset($options['delimiter'])) {
			$this->writer->setDelimiter($options['delimiter']);
			$this->reader->setDelimiter($options['delimiter']);
		}
	}

	static function readToArray($path, $options = array()) {
		$csv = new self($path, $options);
		$rows = array();

		while ($row = $csv->reader->getRow()) {
			$rows[] = $row;
		}

		return $rows;
	}

	public function add() {
		return $this->writer->writeRow(func_get_args());
	}

	public function respond($save_as) {
		$app = \Katu\App::get();

		$app->response->headers->set('Content-Type', 'text/csv; charset=UTF-8');

		return Download::respond($this->path, $save_as, 'inline');
	}

	public function delete() {
		return @unlink($this->path);
	}

}
