<?php

namespace Katu\Tools\Random\LoremIpsum;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class FillText extends \Katu\Tools\Random\LoremIpsum
{
	public static function loadSentences()
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout(static::TIMEOUT), function () {
			$url = \Katu\Types\TURL::make('http://www.filltext.com/', [
				'rows' => 1,
				'lorem' => '{lorem|200}',
			]);
			$src = \Katu\Cache\URL::get($url, new Timeout(static::TIMEOUT));
			$words = (new \Katu\Types\TArray(explode(' ', $src[0]->lorem)))->unique();

			$sentences = new \Katu\Types\TArray;
			while (count($sentences) < static::LOAD_SENTENCES) {
				$sentences[] = ucfirst(implode(' ', $words->shuffle()->slice(0, rand(5, 15))->getArray())) . '.';
			}

			return $sentences;
		});
	}
}
