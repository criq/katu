<?php

namespace Katu\Config;

use Katu\Files\File;
use Katu\Files\FileCollection;

abstract class EnvConfig extends \Katu\Config\Config
{
	public static $variables = null;

	public function getEnvFiles(): FileCollection
	{
		return new FileCollection([
			new File(\App\App::getBaseDir(), ".env"),
		]);
	}

	public function getVariables(): array
	{
		if (is_null(static::$variables)) {
			try {
				$dotenv = \Dotenv\Dotenv::createImmutable(array_map(function (File $file) {
					return (string)$file->getDir();
				}, $this->getEnvFiles()->getArrayCopy()));

				$dotenv->load();
			} catch (\Throwable $e) {
				// Nevermind.
			}

			static::$variables = $_ENV;
		}

		return static::$variables;
	}

	public function getVariable(string $variable): ?string
	{
		return $this->getVariables()[$variable] ?? null;
	}
}
