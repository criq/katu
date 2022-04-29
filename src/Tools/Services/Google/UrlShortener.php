<?php

namespace Katu\Tools\Services\Google;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class UrlShortener
{
	public static function getApiKey(): string
	{
		return \Katu\Config\Config::get("google", "api", "key");
	}

	public static function shorten($url, Timeout $timeout): ?string
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), $timeout, function ($url) {
			$apiUrl = \Katu\Types\TURL::make("https://www.googleapis.com/urlshortener/v1/url", [
				"key" => static::getApiKey(),
			]);

			$curl = new \Curl\Curl;
			$curl->setHeader("Content-Type", "application/json");
			$res = $curl->post($apiUrl, \Katu\Files\Formats\JSON::encode([
				"longUrl" => (string) $url,
			]));

			if (isset($res->id)) {
				return $res->id;
			}

			return false;
		}, $url);
	}
}
