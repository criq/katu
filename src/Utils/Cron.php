<?php

namespace Katu\Utils;

class Cron {

	static function getCurrent() {
		$crons = [];

		$time = (new DateTime)->format('Y-m-d H:i');

		foreach (\Katu\Config::get('cron', 'paths') as $path => $spec) {
			$expression = \Cron\CronExpression::factory($spec);
			if ($expression->getNextRunDate()->format('Y-m-d H:i') > $time) {
				$crons[] = new CronPath($path);
			}
		}

		return $crons;
	}

}
