<?php

namespace Katu\Tools\Random;

abstract class LoremIpsum {

	const TIMEOUT = 86400;
	const LOAD_SENTENCES = 50;
	const SENTENCE_REGEXP = "/\b\p{Lu}.+[\.\?\!]/Uu";

	static function getSentences($count = 1) {
		$sentences = static::loadSentences();

		return $sentences->shuffle()->slice(0, $count);
	}

	abstract static function loadSentences();

}
