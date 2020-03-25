<?php

namespace Katu\Files\Formats;

class CSV
{
	public $file;
	public $reader;
	public $writer;

	public function __construct($file = null, $options = [])
	{
		if ($file) {
			if (is_string($file)) {
				$file = new \Katu\Files\File($file);
			}

			try {
				$file->touch();
			} catch (\Throwable $e) {
				// Nevermind.
			}

			if ((!isset($options['readOnly']) || (isset($options['readOnly']) && !$options['readOnly'])) && !$file->isWritable()) {
				throw new \Exception("Unable to write into specified file.");
			}

			$this->file = $file;
		} else {
			$file = new \Katu\Files\File(\Katu\App::getTmpDir(), 'csv', [\Katu\Tools\Random\Generator::getFileName(), 'csv']);
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

	public function __toString()
	{
		return $this->getAsString();
	}

	public static function readToArray($file, $options = [])
	{
		$options['readOnly'] = true;
		$csv = new static($file, $options);
		$rows = [];

		while ($row = $csv->reader->getRow()) {
			$rows[] = $row;
		}

		return $rows;
	}

	public static function setFromAssoc(array $array, $options = [])
	{
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

	public function add()
	{
		return $this->writer->writeRow(is_array(@func_get_arg(0)) ? func_get_arg(0) : func_get_args());
	}

	public function save($file)
	{
		$file = new \Katu\Files\File($file);
		$file->touch();

		return file_put_contents($file, file_get_contents($this->file));
	}

	public function getAsString()
	{
		return $this->file->get();
	}

	public function delete()
	{
		return @unlink($this->file);
	}
}
