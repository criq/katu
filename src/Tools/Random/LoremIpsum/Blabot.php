<?php

namespace Katu\Tools\Random\LoremIpsum;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;

class Blabot extends \Katu\Tools\Random\LoremIpsum
{
	public static function loadSentences()
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout(static::TIMEOUT), function () {
			$src = \Katu\Cache\URL::get(new TURL('https://www.blabot.cz'), new Timeout(static::TIMEOUT));
			$dom = \Katu\Tools\DOM\DOM::crawlHtml($src);

			$sets = $dom->filter('#blabols p')->each(function ($e) {
				if (preg_match_all(static::SENTENCE_REGEXP, $e->text(), $matches)) {
					return $matches;
				}
			});

			return (new \Katu\Types\TArray($sets))->flatten()->getArray();
		});
	}
}
