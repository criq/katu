<?php

namespace Katu\Tools\System;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class System
{
	public static function getNumberOfCpus(): int
	{
		$cacheIdentifier = new TIdentifier("system", "numberOfCpus");

		return \Katu\Cache\Runtime::get($cacheIdentifier, function () use ($cacheIdentifier) {
			return \Katu\Cache\General::get($cacheIdentifier, new Timeout("1 day"), function () {
				$numCpus = 1;
				if (is_file("/proc/cpuinfo")) {
					$cpuinfo = file_get_contents("/proc/cpuinfo");
					preg_match_all("/^processor/m", $cpuinfo, $matches);
					$numCpus = count($matches[0]);
				} elseif ("WIN" == strtoupper(substr(PHP_OS, 0, 3))) {
					$process = @popen("wmic cpu get NumberOfCores", "rb");
					if (false !== $process) {
						fgets($process);
						$numCpus = intval(fgets($process));
						pclose($process);
					}
				} else {
					$process = @popen("sysctl -a", "rb");
					if (false !== $process) {
						$output = stream_get_contents($process);
						preg_match("/hw.ncpu: (\d+)/", $output, $matches);
						if ($matches) {
							$numCpus = intval($matches[1][0]);
						}
						pclose($process);
					}
				}

				return $numCpus;
			});
		});
	}

	public static function getLoadAverage(): ?array
	{
		return sys_getloadavg() ?: null;
	}

	public static function getLoadAveragePerCpu(): ?array
	{
		$loadAverage = static::getLoadAverage();
		if ($loadAverage) {
			return array_map(function ($i) {
				return $i / static::getNumberOfCpus();
			}, static::getLoadAverage());
		}

		return null;
	}

	public static function assertMaxLoadAverage(float $loadAverage): bool
	{
		if (static::getLoadAveragePerCpu()[0] > $loadAverage) {
			throw new \Katu\Exceptions\LoadAverageExceededException("System load average per CPU is higher than " . $loadAverage . ".");
		}

		return true;
	}
}
