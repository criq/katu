<?php

namespace Katu\Tools\Routing;

class URL
{
	public static function isHttps(): bool
	{
		return isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on";
	}

	public static function getCurrent(): \Katu\Types\TURL
	{
		return new \Katu\Types\TURL("http" . (static::isHttps() ? "s" : null) . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	}

	public static function getBase(): \Katu\Types\TURL
	{
		return new \Katu\Types\TURL(\Katu\Config\Config::get("app", "baseUrl"));
	}

	public static function getFor($route, ?array $args = [], ?array $params = []): \Katu\Types\TURL
	{
		$path = \App\App::get()->getRouteCollector()->getRouteParser()->urlFor($route, array_map("urlencode", (array)$args));

		return \Katu\Types\TURL::make(static::joinPaths(static::getBase()->getHostWithScheme(), $path), $params);
	}

	public static function getDecodedFor($route, $args = [], $params = []): \Katu\Types\TURL
	{
		$path = \App\App::get()->getRouteCollector()->getRouteParser()->urlFor($route, (array)$args);

		return \Katu\Types\TURL::make(static::joinPaths(static::getBase()->getHostWithScheme(), $path), $params);
	}

	public static function joinPaths(): string
	{
		return implode("/", array_map(function ($i) {
			return trim($i, "/");
		}, func_get_args()));
	}
}
