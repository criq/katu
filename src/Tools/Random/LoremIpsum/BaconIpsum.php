<?php

namespace Katu\Tools\Random\LoremIpsum;

class BaconIpsum extends \Katu\Tools\Random\LoremIpsum
{
	public static function loadSentences()
	{
		return \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], static::TIMEOUT, function () {
			$url = \Katu\Types\TURL::make('https://baconipsum.com/api/', [
				'type' => 'meat-and-filler',
				'sentences' => static::LOAD_SENTENCES,
				'start-with-lorem' => 1,
				'format' => 'json',
			]);
			$res = \Katu\Cache\URL::get($url, static::TIMEOUT);

			if (preg_match_all(static::SENTENCE_REGEXP, $res[0], $matches)) {
				return new \Katu\Types\TArray($matches[0]);
			}

			return [];
		});
	}
}
