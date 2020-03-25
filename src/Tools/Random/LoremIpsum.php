<?php

namespace Katu\Tools\Random;

abstract class LoremIpsum
{
	const LOAD_SENTENCES = 50;
	const SENTENCE_REGEXP = "/\b\p{Lu}.+[\.\?\!]/Uu";
	const TIMEOUT = 86400;

	abstract public static function loadSentences();

	public static function getSentences($count = 1)
	{
		$sentences = static::loadSentences();

		return $sentences->shuffle()->slice(0, $count);
	}
}
