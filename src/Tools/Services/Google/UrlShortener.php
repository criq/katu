<?php

namespace Katu\Tools\Services\Google;

class UrlShortener
{
	public static function getApiKey()
	{
		return \Katu\Config\Config::get('google', 'api', 'key');
	}

	public static function shorten($url, $timeout = '1 year')
	{
		return \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], $timeout, function ($url) {
			$apiUrl = \Katu\Types\TURL::make('https://www.googleapis.com/urlshortener/v1/url', [
				'key' => static::getApiKey(),
			]);

			$curl = new \Curl\Curl;
			$curl->setHeader('Content-Type', 'application/json');
			$res = $curl->post($apiUrl, \Katu\Files\Formats\JSON::encode([
				'longUrl' => (string) $url,
			]));

			if (isset($res->id)) {
				return $res->id;
			}

			return false;
		}, $url);
	}
}
