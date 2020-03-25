<?php

namespace Katu\Tools\Random\LoremIpsum;

class Blabot extends \Katu\Tools\Random\LoremIpsum
{
	public static function loadSentences()
	{
		return \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], static::TIMEOUT, function () {
			$src = \Katu\Cache\URL::get('https://www.blabot.cz', static::TIMEOUT);
			$dom = \Katu\Tools\DOM\DOM::crawlHtml($src);

			$sets = $dom->filter('#blabols p')->each(function ($e) {
				if (preg_match_all(static::SENTENCE_REGEXP, $e->text(), $matches)) {
					return $matches;
				}
			});

			return new \Katu\Types\TArray(array_flatten($sets));
		});
	}
}
