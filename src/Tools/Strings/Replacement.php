<?php

namespace Katu\Tools\Strings;

use Katu\Types\TString;

class Replacement
{
	protected $enclosures;
	protected $key;
	protected $value;

	public function __construct(string $key, ?string $value)
	{
		$this->enclosures = [
			new Enclosure("[", "]"),
		];
		$this->key = $key;
		$this->value = $value;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function getKeyRegex(): string
	{
		$array = [];
		for ($position = 0; $position < mb_strlen($this->getKey()); $position++) {
			$char = mb_substr($this->getKey(), $position, 1);
			if ($char == " ") {
				$array[] = "[_\-\s]*";
			} else {
				$array[] = "[" . implode(array_unique([
					mb_strtoupper($char),
					mb_strtolower($char),
					mb_strtoupper((new TString((string)$char))->getForURL()),
					mb_strtolower((new TString((string)$char))->getForURL()),
				])) . "]";
			}
		}

		return "/\[" . implode($array) . "\]/Uui";
	}
}
