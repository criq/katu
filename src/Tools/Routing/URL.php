<?php

namespace Katu\Tools\Routing;

use App\Config\AppConfig;
use Katu\Types\TURL;

class URL
{
	public static function isHttps(): bool
	{
		return isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on";
	}

	public static function getCurrent(): TURL
	{
		return new TURL("http" . (static::isHttps() ? "s" : null) . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	}

	public static function getBase(): TURL
	{
		return (new AppConfig)->getBaseURL();
	}

	public static function getPathFor($route, ?array $args = []): string
	{
		return \App\App::getInstance()->getRouteCollector()->getRouteParser()->urlFor($route, array_map("urlencode", (array)$args));
	}

	public static function getFor($route, ?array $args = [], ?array $params = []): TURL
	{
		return TURL::make(static::joinPaths(static::getBase()->getHostWithScheme(), static::getPathFor($route, $args)), $params);
	}

	public static function getDecodedFor($route, $args = [], $params = []): TURL
	{
		$path = \App\App::getInstance()->getRouteCollector()->getRouteParser()->urlFor($route, (array)$args);

		return TURL::make(static::joinPaths(static::getBase()->getHostWithScheme(), $path), $params);
	}

	public static function joinPaths(): string
	{
		return implode("/", array_map(function ($i) {
			return trim($i, "/");
		}, func_get_args()));
	}
}
