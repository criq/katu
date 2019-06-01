<?php

namespace Katu\Files\Formats;

class CSV {

	public $file;
	public $writer;
	public $reader;

	public function __construct($file = null, $options = []) {
		if ($file) {

			if (is_string($file)) {
				$file = new File($file);
			}

			@$file->touch();

			if ((!isset($options['readOnly']) || (isset($options['readOnly']) && !$options['readOnly'])) && !$file->isWritable()) {
				throw new \Exception("Unable to write into specified file.");
			}

			$this->file = $file;

		} else {

			if (!defined('TMP_PATH')) {
				throw new \Exception("Undefined TMP_PATH.");
			}

			$file = new File(TMP_PATH, 'csv', [Random::getFileName(), 'csv']);
			$file->touch();

			if (!$file->isWritable()) {
				throw new \Exception("Unable to write into a temporary file.");
			}

			$this->file = $file;

		}

		$this->writer = new \EasyCSV\Writer($this->file);
		$this->reader = new \EasyCSV\Reader($this->file, 'r+', isset($options['headersInFirstRow']) ? (bool) $options['headersInFirstRow'] : true);

		if (isset($options['delimiter'])) {
			$this->writer->setDelimiter($options['delimiter']);
			$this->reader->setDelimiter($options['delimiter']);
		}

		if (isset($options['enclosure'])) {
			$this->writer->setEnclosure($options['enclosure']);
			$this->reader->setEnclosure($options['enclosure']);
		}
	}

	static function readToArray($file, $options = []) {
		$options['readOnly'] = true;
		$csv = new static($file, $options);
		$rows = [];

		while ($row = $csv->reader->getRow()) {
			$rows[] = $row;
		}

		return $rows;
	}

	static function setFromAssoc($array, $options = []) {
		$csv = new static(null, $options);
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

		return file_put_contents($saveAs, file_get_contents($this->file));
	}

	public function getAsString() {
		return $this->file->get();
	}

	public function respond($saveAs, $disposition = 'inline') {
		return (new \Katu\Utils\Download($this->file))
			->setMime('text/csv')
			->setCharset('utf-8')
			->respond($saveAs, $disposition)
			;
	}

	public function delete() {
		return @unlink($this->file);
	}

}
