<?php

namespace Katu\Utils;

class Cron {

	static function getCurrent() {
		$crons = [];

		try {

			$time = (new DateTime);
			$time->setTime($time->format('H'), $time->format('i'), 0);

			$paths = array_filter((array)\Katu\Config::get('cron', 'paths'));
			foreach ($paths as $path => $spec) {
				$expression = \Cron\CronExpression::factory($spec);
				$nextRunTime = $expression->getNextRunDate($time, 0, true);
				if ($time == $nextRunTime) {
					$crons[] = new CronPath($path);
				}
			}

		} catch (\Katu\Exceptions\MissingConfigException $e) {
			/* Nevermind. */
		}

		return $crons;
	}

	static function runCurrent() {
		$crons = static::getCurrent();
		foreach ($crons as $cron) {
			$cron->run();
		}

		return true;
	}

}
