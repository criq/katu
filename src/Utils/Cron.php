<?php

namespace Katu\Utils;

class Cron {

	static function getCurrent() {
		$crons = [];

		$time = (new DateTime)->format('Y-m-d H:i');
		var_dump($time);

		foreach (\Katu\Config::get('cron', 'paths') as $path => $spec) {
			$expression = \Cron\CronExpression::factory($spec);
			var_dump($expression->getNextRunDate()->format('Y-m-d H:i'));
			if ($expression->getNextRunDate()->format('Y-m-d H:i') == $time) {
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
