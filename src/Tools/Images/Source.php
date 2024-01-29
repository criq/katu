<?php

namespace Katu\Tools\Images;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;
use Katu\Types\TURL;

abstract class Source implements PackagedInterface
{
	protected $input;

	abstract public function getExtension(): ?string;
	abstract public function getLocalFile(): ?\Katu\Files\File;
	abstract public function getURI(): string;
	abstract public function getURL(): ?TURL;

	public function __construct($input)
	{
		$this->input = $input;
	}

	public static function createFromPackage(Package $package): Source
	{
		$className = TClass::createFromPortableName($package->getPayload()["class"])->getName();

		return $className::createFromPackage($package);
	}

	public static function createFromInput($input): ?Source
	{
		// Image.
		if ($input instanceof Image) {
			return $input->getSource();

		// Image source.
		} elseif ($input instanceof static) {
			return $input;

		// File on filesystem.
		} elseif ($input instanceof \Katu\Files\File) {
			return new Sources\File($input);

		// URL.
		} elseif ($input instanceof \Katu\Types\TURL) {
			return new Sources\URL($input);

		// File model.
		} elseif ($input instanceof \Katu\Models\Presets\File) {
			return new Sources\FileModel($input);

		// String.
		} elseif (is_string($input)) {
			try {
				return new Sources\URL(new \Katu\Types\TURL($input));
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				$file = new \Katu\Files\File($input);
				if ($file->exists()) {
					return new Sources\File($file);
				}

				throw new \Exception;
			} catch (\Throwable $e) {
				// Nevermind.
			}
		}

		return null;
	}

	public function getInput()
	{
		return $this->input;
	}

	public function getHash(): string
	{
		return sha1($this->getURI());
	}
}
