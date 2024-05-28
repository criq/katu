<?php

namespace Katu\Types;

use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;
use Symfony\Component\String\UnicodeString;

class TString implements \Stringable
{
	protected $string;

	public function __construct(string $string)
	{
		$this->setString($string);
	}

	public function __toString(): string
	{
		return (string)$this->string;
	}

	public function setString(string $string): TString
	{
		$this->string = $string;

		return $this;
	}

	public function getString(): string
	{
		return $this->string;
	}

	public function getNumberOfWords(): int
	{
		return count(array_filter(preg_split("/\s+/", $this->getString())));
	}

	public function hasAtLeastWords(int $words): bool
	{
		return $this->getNumberOfWords() >= $words;
	}

	public function getAsArray(): array
	{
		$chars = [];
		for ($i = 0; $i < mb_strlen($this->string); $i++) {
			$chars[] = mb_substr($this->string, $i, 1);
		}

		return $chars;
	}

	public function getWbr(): TString
	{
		return new static(implode("<wbr />", $this->getAsArray()));
	}

	public function getWithNormalizedSpaces(): TString
	{
		return new static(str_replace("\xc2\xa0", "\x20", $this));
	}

	public function getTrimmed(): TString
	{
		return new static(trim($this));
	}

	public function getIsFloat(): bool
	{
		return preg_match("/^\s*(([0-9]+([,\.][0-9]+)?)|([,\.][0-9]+))\s*$/", $this);
	}

	public function getAsFloat(): float
	{
		return (float)floatval(trim(strtr(preg_replace("/\s/u", "", (string)$this->getTrimmed()), ",", ".")));
	}

	public function getAsFloatIfNumeric()
	{
		return $this->getIsFloat() ? $this->getAsFloat() : $this;
	}

	public function getForURL(?OptionCollection $options = null): TString
	{
		$options = (new OptionCollection([
			new Option("SEPARATOR", "-"),
			new Option("LANGUAGE", "en"),
			new Option("LOWERCASE", true),
			new Option("MAX_LENGTH", 255),
		]))->getMergedWith($options);

		\URLify::$remove_list = [];

		$string = \URLify::filter(
			$this->getString(),
			$options->getValue("MAX_LENGTH"),
			$options->getValue("LANGUAGE"),
			false,
			false,
			(bool)$options->getValue("LOWERCASE"),
			$options->getValue("SEPARATOR")
		);

		return new static($string);
	}

	public function getWithRemovedWhitespace(): TString
	{
		return new static(preg_replace("/\s/", "", $this));
	}

	public function getWithAccentsRemoved(): TString
	{
		$string = strtr($this, [
			"á" => "a",
			"Á" => "A",
			"č" => "c",
			"Č" => "C",
			"ď" => "d",
			"Ď" => "D",
			"é" => "e",
			"É" => "E",
			"ě" => "e",
			"Ě" => "E",
			"í" => "i",
			"Í" => "I",
			"ň" => "n",
			"Ň" => "N",
			"ó" => "o",
			"Ó" => "O",
			"ř" => "r",
			"Ř" => "R",
			"š" => "s",
			"Š" => "S",
			"ť" => "t",
			"Ť" => "T",
			"ú" => "u",
			"Ú" => "U",
			"ů" => "u",
			"Ů" => "U",
			"ý" => "y",
			"Ý" => "Y",
			"ž" => "z",
			"Ž" => "Z",
		]);

		return new static($string);
	}

	public function getSearchable(): TString
	{
		$string = (string)(new TString($this->string))->getWithNormalizedSpaces()->getTrimmed();
		$string = mb_strtolower($string);
		$string = (new static($string))->getWithAccentsRemoved();

		// Remove non-word characters.
		$string = preg_replace("/\s+/", "_", $string);
		$string = preg_replace("/\W/", " ", $string);
		$string = preg_replace("/_/", " ", $string);

		// Remove excess whitespace.
		$string = preg_replace("/\s+/", " ", $string);

		return new static($string);
	}

	public function getLowercase(): TString
	{
		return new static(mb_strtolower($this));
	}

	public function getIsWithoutDiacritics(): TString
	{
		return new static((new UnicodeString((string)$this))->ascii()->toString());
	}
}
