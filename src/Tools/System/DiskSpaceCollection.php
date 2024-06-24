<?php

namespace Katu\Tools\System;

use Katu\Types\TFileSize;

class DiskSpaceCollection extends \ArrayObject
{
	public function __construct(array $array)
	{
		foreach ($array as $diskSpace) {
			$this[] = $diskSpace;
		}
	}

	public function offsetSet($key, $value)
	{
		parent::offsetSet($value->getMount(), $value);
	}

	public static function createDefault(): DiskSpaceCollection
	{
		switch (PHP_OS) {
			case "Darwin":
				$command = 'df | awk \'NR>1{print $1"\t"$2"\t"$3"\t"$4"\t"$5"\t"$9}\'';
				break;
			default:
				$command = 'df | awk \'NR>1{print $1"\t"$2"\t"$3"\t"$4"\t"$5"\t"$6}\'';
				break;
		}

		exec($command, $output);

		return new DiskSpaceCollection(array_map(function (string $row) {
			$parts = preg_split("/\t/", $row);

			return new DiskSpace($parts[0], $parts[5], new TFileSize($parts[1] * 1024), new TFileSize($parts[2] * 1024));
		}, $output));
	}

	public function getByMount(string $mount): ?DiskSpace
	{
		return $this[$mount] ?? null;
	}
}
