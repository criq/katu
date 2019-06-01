<?php

namespace Katu\Utils;

use \Symfony\Component\DomCrawler\Crawler;

class DOM {

	static function crawlHtml($src) {
		$crawler = new Crawler();
		$crawler->addHtmlContent($src);

		return $crawler;
	}

	static function crawlXml($src) {
		$crawler = new Crawler();
		$crawler->addXmlContent($src);

		return $crawler;
	}

	static function crawlUrl($url, $timeout = null) {
		return static::crawlHtml(Cache::geTURL($url, $timeout));
	}

}
