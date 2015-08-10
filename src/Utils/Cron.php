<?php

namespace Katu\Utils;

class Cron {

	static function getCurrent() {
		$crons = [];

		$time = (new DateTime);
		$time->setTime($time->format('H'), $time->format('i'), -1);

		foreach (\Katu\Config::get('cron', 'paths') as $path => $spec) {
			$expression = \Cron\CronExpression::factory($spec);
			if ($expression->getNextRunDate($time)->format('Y-m-d H:i:s') == $time) {
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
