<?php

namespace Katu\Utils;

class CSV {

	public $path;
	public $writer;
	public $reader;

	public function __construct($path = null, $options = []) {
		if ($path) {

			FileSystem::touch($path);

			if ((!isset($options['readOnly']) || (isset($options['readOnly']) && !$options['readOnly'])) && !is_writable($path)) {
				throw new \Exception("Unable to write into specified file.");
			}

			$this->path = $path;

		} else {

			if (!defined('TMP_PATH')) {
				throw new \Exception("Undefined TMP_PATH.");
			}

			$path = FileSystem::joinPaths(TMP_PATH, FileSystem::getPathForName([
				'!csv',
				Random::getFileName(),
			]));
			FileSystem::touch($path);

			if (!is_writable($path)) {
				throw new \Exception("Unable to write into a temporary file.");
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

	static function readToArray($path, $options = []) {
		$options['readOnly'] = true;
		$csv = new self($path, $options);
		$rows = [];

		while ($row = $csv->reader->getRow()) {
			$rows[] = $row;
		}

		return $rows;
	}

	static function setFromAssoc($array, $options = []) {
		$csv = new self(null, $options);
		$line = 0;

		foreach ($array as $row) {
			if (++$line == 1) {
				$csv->add(array_keys($row));
			}
			$csv->add(array_values($row));
		}

		return $csv;
	}

	public function add() {
		return $this->writer->writeRow(is_array(@func_get_arg(0)) ? func_get_arg(0) : func_get_args());
	}

	public function save($saveAs) {
		FileSystem::touch($saveAs);

		return file_put_contents($saveAs, file_get_contents($this->path));
	}

	public function respond($saveAs) {
		$app = \Katu\App::get();

		$app->response->headers->set('Content-Type', 'text/csv; charset=UTF-8');

		return Download::respond($this->path, $saveAs, 'inline');
	}

	public function delete() {
		return @unlink($this->path);
	}

}
