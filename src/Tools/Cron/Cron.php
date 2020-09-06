<?php

namespace Katu\Tools\Cron;

class Cron
{
	public static function getCurrent()
	{
		$crons = [];

		$time = new \Katu\Tools\DateTime\DateTime;
		$time->setTime($time->format('H'), $time->format('i'), 0);

		try {
			$paths = array_filter((array)\Katu\Config\Config::get('cron', 'paths'));
			foreach ($paths as $path => $spec) {
				if (is_array($spec)) {
					list($path, $spec) = [key($spec), current($spec)];
				}
				$expression = \Cron\CronExpression::factory($spec);
				$nextRunTime = $expression->getNextRunDate($time, 0, true);
				if ($time == $nextRunTime) {
					$crons[] = new Path($path);
				}
			}
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nevermind.
		}

		try {
			$routes = array_filter((array)\Katu\Config\Config::get('cron', 'routes'));
			foreach ($routes as $route => $spec) {
				if (is_array($spec)) {
					list($route, $spec) = [key($spec), current($spec)];
				}
				$expression = \Cron\CronExpression::factory($spec);
				$nextRunTime = $expression->getNextRunDate($time, 0, true);
				if ($time == $nextRunTime) {
					$crons[] = new Route($route);
				}
			}
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nevermind.
		}

		return $crons;
	}

	public static function runCurrent(?array $options = [])
	{
		$crons = static::getCurrent();
		foreach ($crons as $cron) {
			$cron->run();
			if ($options['sleep'] ?? null) {
				sleep($options['sleep']);
			}
		}

		return true;
	}
}
