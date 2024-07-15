<?php

namespace Katu\Types;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;

class TIdentifier implements PackagedInterface
{
	const HASH_ALGO = "crc32";

	protected $parts;

	public function __construct()
	{
		$this->setParts(...func_get_args());
	}

	public function __toString(): string
	{
		return (string)$this->getPath();
	}

	public static function createFromPackage(Package $package): TIdentifier
	{
		return new static(...$package->getPayload()["parts"]);
	}

	public function getPackage(): Package
	{
		return new Package([
			"parts" => $this->getParts(),
		]);
	}

	public function setParts(): TIdentifier
	{
		$this->parts = func_get_args();

		return $this;
	}

	public function getParts(): array
	{
		return $this->parts;
	}

	public function getChecksum(): string
	{
		return hash(static::HASH_ALGO, serialize($this->getParts()));
	}

	public function getSanitizedParts(): array
	{
		/**************************************************************************
		 * Make sure input in an array.
		 */
		$parts = $this->getParts();
		// var_dump($parts);die;

		/**************************************************************************
		 * Flatten array.
		 */
		$parts = (new \Katu\Types\TArray($parts))->flatten()->getArray();
		// var_dump($parts);die;

		/**************************************************************************
		 * Make sure everything is a string.
		 */
		$parts = array_map(function ($i) {
			try {
				return (string)$i;
			} catch (\Throwable $e) {
				return md5(serialize($i));
			}
		}, $parts);
		// var_dump($parts);die;

		/**************************************************************************
		 * Separate into directories.
		 */
		$parts = array_map(function ($i) {
			return preg_split("/[\/\\\\&\?=]/", $i);
		}, $parts);
		// var_dump($parts);die;

		/**************************************************************************
		 * Flatten array.
		 */
		$parts = (new \Katu\Types\TArray($parts))->flatten()->getArray();
		// var_dump($parts);die;

		/**************************************************************************
		 * Underscore capital letters.
		 */
		$parts = array_map(function ($i) {
			return preg_replace_callback("/\p{Lu}/u", function ($matches) {
				return "_" . mb_strtolower($matches[0]);
			}, $i);
		}, $parts);
		// var_dump($parts);die;

		/**************************************************************************
		 * Sanitize dashes and underscores.
		 */
		$parts = array_map(function ($i) {
			$i = strtr($i, "-", "_");
			$i = trim($i, "_");

			return $i;
		}, $parts);
		// var_dump($parts);die;

		/**************************************************************************
		 * Remove invalid characters.
		 */
		$parts = array_map(function ($i) {
			$i = strtr($i, "\\", "/");
			$i = mb_strtolower($i);
			$i = preg_replace("/[^a-z0-9_\/\.]/i", "", $i);
			return $i;
		}, $parts);
		// var_dump($parts);die;

		/**************************************************************************
		 * Filter.
		 */
		$parts = array_values(array_filter($parts));
		// var_dump($parts);die;

		return $parts;
	}

	public function getPathParts(?string $extension = null): array
	{
		$parts = $this->getSanitizedParts();

		try {
			$filename = array_slice($parts, -1, 1)[0];
			$pathinfo = pathinfo($filename);

			$hashedFilename = implode(".", array_filter([
				$pathinfo["filename"],
				$this->getChecksum(),
				$extension ?: ($pathinfo["extension"] ?? null),
			]));

			$parts = array_merge(array_slice($parts, 0, -1), [
				$hashedFilename,
			]);
		} catch (\Throwable $e) {
			$parts[] = $this->getChecksum();
		}

		return $parts;
	}

	public function getPath(?string $extension = null): string
	{
		return implode("/", $this->getPathParts($extension));
	}

	public function getKey(): string
	{
		$key = $this->getPath();
		if (strlen($key) > 250) {
			$key = md5($key);
		}

		return $key;
	}

	public function getNamespacedKey(): string
	{
		return implode(":", [
			\Katu\Config\Env::getPlatform(),
			\Katu\Config\Env::getVersion(),
			$this->getKey(),
		]);
	}
}
