<?php

namespace Katu\Tools\DOM;

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

	public static function crawlUrl($url, $timeout = null)
	{
		return static::crawlHtml(\Katu\Cache\URL::get($url, $timeout));
	}
}
