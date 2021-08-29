<?php

namespace Katu\Utils;

class System
{
	public static function getNumberOfCpus()
	{
		return Cache::getFromMemory('system.numberOfCpus', function () {
			$numCpus = 1;
			if (is_file('/proc/cpuinfo')) {
				$cpuinfo = file_get_contents('/proc/cpuinfo');
				preg_match_all('/^processor/m', $cpuinfo, $matches);
				$numCpus = count($matches[0]);
			} elseif ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
				$process = @popen('wmic cpu get NumberOfCores', 'rb');
				if (false !== $process) {
					fgets($process);
					$numCpus = intval(fgets($process));
					pclose($process);
				}
			} else {
				$process = @popen('sysctl -a', 'rb');
				if (false !== $process) {
					$output = stream_get_contents($process);
					preg_match('/hw.ncpu: (\d+)/', $output, $matches);
					if ($matches) {
						$numCpus = intval($matches[1][0]);
					}
					pclose($process);
				}
			}

			return $numCpus;
		});
	}

	public static function getLoadAverage()
	{
		return sys_getloadavg();
	}

	public static function getLoadAveragePerCpu()
	{
		return array_map(function ($i) {
			return $i / static::getNumberOfCpus();
		}, static::getLoadAverage());
	}

	public static function assertMaxLoadAverage($loadAverage)
	{
		if (static::getLoadAveragePerCpu()[0] > $loadAverage) {
			throw new \Katu\Exceptions\LoadAverageExceededException("System load average per CPU is higher than " . $loadAverage . ".");
		}

		return true;
	}

	public static function getDiskSpace()
	{
		$res = [];
		$output = shell_exec('df -kP | awk \'{printf "%-32s\t%16d\t%16d\t%16d\t%16d\t%s\n", $1, $2, $3, $4, $5, $6}\'');
		foreach (array_values(array_filter(preg_split('/\v/', $output))) as $key => $row) {
			if ($key > 0) {
				$array = preg_split('/\t/', $row);
				$res[trim($array[0])] = [
					'filesystem' => trim($array[0]),
					'mount' => trim($array[5]),
					'capacity' => new FileSize((int)trim($array[1]) * 1024),
					'used' => new FileSize((int)trim($array[2]) * 1024),
					'free' => new FileSize(((int)trim($array[1]) * 1024) - ((int)trim($array[2]) * 1024)),
				];
			}
		}

		return $res;
	}
}
