<?php

namespace Katu\Config;

class Env
{
	const ENV_DEV = 0;
	const ENV_PROD = 1;

	public static function getPlatform() : string
	{
		$files = [
			new \Katu\Files\File(\Katu\App::getBaseDir(), ".platform"),
			new \Katu\Files\File(\Katu\App::getBaseDir(), "app", ".platform"),
		];

		foreach ($files as $file) {
			if ($file->exists() && $file->isReadable()) {
				return trim($file->get());
			}
		}

		return false;
	}

	public static function getHash() : string
	{
		return sha1(\Katu\Files\Formats\JSON::encodeStandard(self::getEnvProperties()));
	}

	public static function getShortHash() : string
	{
		$hash = self::getHash();

		return substr($hash, 0, 4) . substr($hash, -4, 4);
	}

	public static function getEnvProperties() : array
	{
		return [
			"host" => $_SERVER["SERVER_NAME"],
			"dir" => \Katu\App::getBaseDir(),
		];
	}

	public static function getCommit() : ?string
	{
		try {
			$file = new \Katu\Files\File(\Katu\App::getBaseDir(), ".git", "HEAD");
			preg_match("/ref: (.+)/", $file->get(), $match);
			$file = new \Katu\Files\File(".git", $match[1]);
			return trim($file->get());
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function getVersion() : string
	{
		return hash("adler32", implode("", [
			static::getCommit(),
			static::getHash(),
		]));
	}
}
