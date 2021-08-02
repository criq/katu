<?php

namespace Katu\Tools\DOM;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TURL;
use Symfony\Component\DomCrawler\Crawler;

class DOM
{
	public static function crawlHtml($src)
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent($src);

		return $crawler;
	}

	public static function crawlXml($src)
	{
		$crawler = new Crawler();
		$crawler->addXmlContent($src);

		return $crawler;
	}

	public static function crawlUrl(TURL $url, Timeout $timeout)
	{
		return static::crawlHtml(\Katu\Cache\URL::get($url, $timeout));
	}
}
