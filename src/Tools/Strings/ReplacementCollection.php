<?php

namespace Katu\Tools\Strings;

class ReplacementCollection extends \ArrayObject
{
	public function apply(string $input): string
	{
		$output = $input;
		foreach ($this as $replacement) {
			$output = preg_replace($replacement->getKeyRegex(), $replacement->getValue(), $output);
		}

		return $output;
	}
}
