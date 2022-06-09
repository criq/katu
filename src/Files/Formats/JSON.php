<?php

namespace Katu\Files\Formats;

use Katu\Types\TJSON;

class JSON
{
	public static function getEncodeBitmask()
	{
		return
			  (defined("JSON_PRETTY_PRINT")      ? JSON_PRETTY_PRINT      : null)
			| (defined("JSON_UNESCAPED_SLASHES") ? JSON_UNESCAPED_SLASHES : null)
			| (defined("JSON_UNESCAPED_UNICODE") ? JSON_UNESCAPED_UNICODE : null)
		;
	}

	public static function getInlineEncodeBitmask()
	{
		return
			  (defined("JSON_UNESCAPED_SLASHES") ? JSON_UNESCAPED_SLASHES : null)
			| (defined("JSON_UNESCAPED_UNICODE") ? JSON_UNESCAPED_UNICODE : null)
		;
	}

	public static function encode($var)
	{
		return new TJSON(json_encode($var, static::getEncodeBitmask()));
	}

	public static function encodeInline($var)
	{
		return new TJSON(json_encode($var, static::getInlineEncodeBitmask()));
	}

	public static function encodeStandard($var)
	{
		return new TJSON(json_encode($var));
	}

	public static function decodeAsObjects($var)
	{
		return @json_decode($var, false);
	}

	public static function decodeAsArray($var)
	{
		return @json_decode($var, true);
	}
}
