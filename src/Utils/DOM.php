<?php

namespace Katu\Utils;

use \Symfony\Component\DomCrawler\Crawler;

class DOM {

	static function crawlHtml($src) {
		$crawler = new Crawler();
		$crawler->addHtmlContent($src);

		return $crawler;
	}

}
