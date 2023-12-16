<?php

namespace Katu\Types;

use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;

class TString
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
		return count(array_filter(preg_split("/\s/", $this->getString())));
	}

	public function hasAtLeastWords(int $words): bool
	{
		return $this->getNumberOfWords() >= $words;
	}

	public function getForURL(?OptionCollection $options = null): string
	{
		$options = (new OptionCollection([
			new Option("SEPARATOR", "-"),
			new Option("LANGUAGE", "en"),
			new Option("LOWERCASE", true),
			new Option("MAX_LENGTH", 255),
		]))->getMergedWith($options);

		\URLify::$remove_list = [];

		return \URLify::filter(
			$this->getString(),
			$options->getValue("MAX_LENGTH"),
			$options->getValue("LANGUAGE"),
			false,
			false,
			(bool)$options->getValue("LOWERCASE"),
			$options->getValue("SEPARATOR")
		);
	}

	public function getIsFloat(): bool
	{
		return preg_match("/^\s*(([0-9]+([,\.][0-9]+)?)|([,\.][0-9]+))\s*$/", $this->getString());
	}

	public function getAsFloat(): float
	{
		return (float)floatval(trim(strtr(preg_replace("/\s/u", "", (string)$this->trim()), ",", ".")));
	}

	public function getAsFloatIfNumeric()
	{
		return $this->getIsFloat() ? $this->getAsFloat() : $this;
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

	public function normalizeSpaces(): TString
	{
		$this->setString(str_replace("\xc2\xa0", "\x20", $this));

		return $this;
	}

	public function trim(): TString
	{
		$this->setString(trim($this));

		return $this;
	}

	public function ucfirst(): TString
	{
		$this->setString(ucfirst($this));

		return $this;
	}
}
