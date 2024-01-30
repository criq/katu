<?php

namespace Katu\Tools\Strings;

use Katu\Types\TString;

class AccentedString
{
	public $string;

	public function __construct(string $string)
	{
		$this->string = (string)$string;
	}

	public function __toString(): string
	{
		return (string)$this->string;
	}

	public function getSearchable(): string
	{
		$string = (string)(new TString($this->string))->normalizeSpaces()->trim();
		$string = mb_strtolower($string);
		$string = strtr($string, [
			"á" => "a",
			"č" => "c",
			"ď" => "d",
			"é" => "e",
			"ě" => "e",
			"í" => "i",
			"ň" => "n",
			"ó" => "o",
			"ř" => "r",
			"š" => "s",
			"ť" => "t",
			"ú" => "u",
			"ů" => "u",
			"ý" => "y",
			"ž" => "z",
		]);

		// Remove non-word characters.
		$string = preg_replace("/\s+/", "_", $string);
		$string = preg_replace("/\W/", " ", $string);
		$string = preg_replace("/_/", " ", $string);

		// // Remove excess whitespace.
		$string = preg_replace("/\s+/", " ", $string);

		return $string;
	}
}
