<?php

namespace Katu\Files\Formats;

use App\Classes\Option;
use App\Classes\OptionCollection;

class CSV extends \ArrayObject
{
	protected $file;
	protected $reader;
	protected $writer;

	public function __construct(?\Katu\Files\File $file = null, ?OptionCollection $options = null)
	{
		$this->setFile($file);
		$this->setOptions((new OptionCollection([
			new Option("delimiter", ","),
			new Option("enclosure", "\""),
			new Option("readOnly", false),
		]))->mergeWith($options));
	}

	public function __toString(): string
	{
		return $this->getFile()->get();
	}

	public static function createFromArray(array $array, ?OptionCollection $options = null): CSV
	{
		return (new static(null, $options))->setRecords($array);
	}

	public static function createFromFile(\Katu\Files\File $file, ?OptionCollection $options = null): CSV
	{
		return (new static($file, $options))->readRecords();
	}

	public function setFile(?\Katu\Files\File $file): CSV
	{
		$this->file = $file;

		return $this;
	}

	public function getFile(): \Katu\Files\File
	{
		if (!$this->file) {
			$this->file = new \Katu\Files\File(\App\App::getTemporaryDir(), "csv", [\Katu\Tools\Random\Generator::getFileName(), "csv"]);
			$this->file->touch();
		}

		return $this->file;
	}

	public function setOptions(OptionCollection $options): CSV
	{
		$this->options = $options;

		return $this;
	}

	public function getOptions(): OptionCollection
	{
		return $this->options;
	}

	public function setDelimiter(string $delimiter): CSV
	{
		$this->getOptions()->mergeWith(new OptionCollection([
			new Option("delimiter", $delimiter),
		]));

		return $this;
	}

	public function getDelimiter(): string
	{
		return $this->getOptions()->getValue("delimiter");
	}

	public function setEnclosure(string $enclosure): CSV
	{
		$this->getOptions()->mergeWith(new OptionCollection([
			new Option("enclosure", $enclosure),
		]));

		return $this;
	}

	public function getEnclosure(): string
	{
		return $this->getOptions()->getValue("enclosure");
	}

	public function getReader(): \League\Csv\Reader
	{
		if (!$this->reader) {
			$this->reader = \League\Csv\Reader::createFromPath($this->getFile())
				->setHeaderOffset(0)
				->setDelimiter($this->getDelimiter())
				->setEnclosure($this->getEnclosure())
				;
		}

		return $this->reader;
	}

	public function getWriter(): \League\Csv\Writer
	{
		if (!$this->writer) {
			$this->writer = \League\Csv\Writer::createFromPath($this->getFile(), "w+")
				->setDelimiter($this->getDelimiter())
				->setEnclosure($this->getEnclosure())
				;
		}

		$agent = new \Jenssegers\Agent\Agent;
		if (in_array($agent->platform(), ["Windows", "Win"])) {
			$this->writer
				->setDelimiter(";")
				->setOutputBOM(\League\Csv\Writer::BOM_UTF8)
				// ->addStreamFilter("convert.iconv.ISO-8859-2/UTF-8")
				;
		} elseif ($agent->platform() == "OS X") {
			$this->writer
				->setOutputBOM(\League\Csv\Writer::BOM_UTF16_LE)
				;
		}

		return $this->writer;
	}

	public function setRecords(array $records): CSV
	{
		foreach ($records as $record) {
			$this[] = $record;
		}

		$this->writeRecords();

		return $this;
	}

	public function readRecords(): CSV
	{
		$this->setRecords(iterator_to_array($this->getReader()->getRecords()));

		return $this;
	}

	public function writeRecords(): CSV
	{
		$this->getWriter()->insertOne(array_keys($this->getRecords()[0] ?? []));
		$this->getWriter()->insertAll($this->getRecords());

		return $this;
	}

	public function getRecords(): array
	{
		return $this->getArrayCopy();
	}

	public function delete(): bool
	{
		return $this->getFile()->delete();
	}
}
