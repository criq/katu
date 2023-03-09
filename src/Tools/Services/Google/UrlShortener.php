<?php

namespace Katu\Tools\Services\Google;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;

class URLShortener
{
	public static function getAPIKey(): string
	{
		return \Katu\Config\Config::get("google", "api", "key");
	}

	public static function shorten($url, Timeout $timeout): ?TURL
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), $timeout, function ($url) {
			$apiUrl = \Katu\Types\TURL::make("https://www.googleapis.com/urlshortener/v1/url", [
				"key" => static::getAPIKey(),
			]);

			$curl = new \Curl\Curl;
			$curl->setHeader("Content-Type", "application/json");
			$res = $curl->post($apiUrl, \Katu\Files\Formats\JSON::encode([
				"longUrl" => (string) $url,
			]));

			if (isset($res->id)) {
				return new TURL($res->id);
			}

			return null;
		}, $url);
	}
}
