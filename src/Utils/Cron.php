<?php

namespace Katu\Utils;

class Cron {

	static function getCurrent() {
		$crons = [];

		$time = (new DateTime);
		$time->setTime($time->format('H'), $time->format('i'), 0);

		foreach (\Katu\Config::get('cron', 'paths') as $path => $spec) {
			$expression = \Cron\CronExpression::factory($spec);
			$nextRunTime = $expression->getNextRunDate($time, 0, true);
			if ($time == $nextRunTime) {
				$crons[] = new CronPath($path);
			}
		}

		return $crons;
	}

	static function runCurrent() {
		foreach (static::getCurrent() as $cron) {
			$cron->run();
		}

		return true;
	}

}
