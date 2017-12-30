<?php

namespace Katu\Utils;

class Cron {

	static function getCurrent() {
		$crons = [];

		$time = new DateTime;
		$time->setTime($time->format('H'), $time->format('i'), 0);

		try {

			$paths = array_filter((array)\Katu\Config::get('cron', 'paths'));
			foreach ($paths as $path => $spec) {
				if (is_array($spec)) {
					list($path, $spec) = [key($spec), current($spec)];
				}
				$expression = \Cron\CronExpression::factory($spec);
				$nextRunTime = $expression->getNextRunDate($time, 0, true);
				if ($time == $nextRunTime) {
					$crons[] = new CronPath($path);
				}
			}

		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nevermind.
		}

		try {

			$routes = array_filter((array)\Katu\Config::get('cron', 'routes'));
			foreach ($routes as $route => $spec) {
				if (is_array($spec)) {
					list($route, $spec) = [key($spec), current($spec)];
				}
				$expression = \Cron\CronExpression::factory($spec);
				$nextRunTime = $expression->getNextRunDate($time, 0, true);
				if ($time == $nextRunTime) {
					$crons[] = new CronRoute($route);
				}
			}

		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nevermind.
		}

		return $crons;
	}

	static function runCurrent() {
		$crons = static::getCurrent();
		foreach ($crons as $cron) {
			var_dump($cron);
			var_dump($cron->run());
		}

		return true;
	}

}
