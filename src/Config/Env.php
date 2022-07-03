<?php

namespace Katu\Config;

class Env
{
	const ENV_DEV = 0;
	const ENV_PROD = 1;

	public static function getPlatform(): string
	{
		$files = [
			new \Katu\Files\File(\App\App::getBaseDir(), ".platform"),
			new \Katu\Files\File(\App\App::getBaseDir(), "app", ".platform"),
		];

		foreach ($files as $file) {
			if ($file->exists() && $file->isReadable()) {
				return trim($file->get());
			}
		}

		return false;
	}

	public static function getHash(): string
	{
		return sha1(\Katu\Files\Formats\JSON::encodeStandard(self::getEnvProperties()));
	}

	public static function getShortHash(): string
	{
		$hash = self::getHash();

		return substr($hash, 0, 4) . substr($hash, -4, 4);
	}

	public static function getEnvProperties(): array
	{
		return [
			"host" => $_SERVER["SERVER_NAME"],
			"dir" => \App\App::getBaseDir(),
		];
	}

	public static function getCommit(): ?string
	{
		try {
			exec("git log --pretty=\"%H\" -n1 HEAD", $output);
			return $output[0];
		} catch (\Throwable $e) {
			try {
				$file = new \Katu\Files\File(\App\App::getBaseDir(), ".git", "logs", "HEAD");
				$lines = $file->getLines();
				$line = trim($lines[count($lines)-1]);
				preg_match("/^(?<commit>[0-9a-f]{40})/", $line, $match);
				return $match["commit"];
			} catch (\Throwable $e) {
			}
		}

		return null;
	}

	public static function getVersion(): string
	{
		return hash("adler32", implode("", [
			static::getCommit(),
			static::getHash(),
		]));
	}
}
