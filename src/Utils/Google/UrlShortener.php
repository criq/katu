<?php

namespace Katu\Utils\Google;

use \Katu\Config;

class UrlShortener {

	static function getApiKey() {
		return Config::get('google', 'api', 'key');
	}

	static function shorten($url) {
		return \Katu\Utils\Cache::get(function($url) {

			$apiUrl = \Katu\Types\TUrl::make('https://www.googleapis.com/urlshortener/v1/url', [
				'key' => static::getApiKey(),
			]);

			$curl = new \Curl\Curl;
			$curl->setHeader('Content-Type', 'application/json');
			$res = $curl->post($apiUrl, \Katu\Utils\JSON::encode([
				'longUrl' => (string) $url,
			]));

			if (isset($res->id)) {
				return $res->id;
			}

			return false;

		}, 86400 * 365, $url);
	}

}
