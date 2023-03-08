<?php

namespace Katu\Tools\DOM;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TURL;
use Symfony\Component\DomCrawler\Crawler;

class DOM
{
	public static function crawlHTML($src)
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent($src);

		return $crawler;
	}

	public static function crawlXML($src)
	{
		$crawler = new Crawler();
		$crawler->addXmlContent($src);

		return $crawler;
	}

	public static function crawlURL(TURL $url, Timeout $timeout)
	{
		return static::crawlHTML(\Katu\Cache\URL::get($url, $timeout));
	}
}
