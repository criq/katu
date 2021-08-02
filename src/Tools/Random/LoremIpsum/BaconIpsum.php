<?php

namespace Katu\Tools\Random\LoremIpsum;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class BaconIpsum extends \Katu\Tools\Random\LoremIpsum
{
	public static function loadSentences()
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout(static::TIMEOUT), function () {
			$url = \Katu\Types\TURL::make('https://baconipsum.com/api/', [
				'type' => 'meat-and-filler',
				'sentences' => static::LOAD_SENTENCES,
				'start-with-lorem' => 1,
				'format' => 'json',
			]);
			$res = \Katu\Cache\URL::get($url, new Timeout(static::TIMEOUT));

			if (preg_match_all(static::SENTENCE_REGEXP, $res[0], $matches)) {
				return new \Katu\Types\TArray($matches[0]);
			}

			return [];
		});
	}
}
