<?php

namespace Katu\Tools\Calendar;

class IntervalCollection extends \ArrayObject
{
	public function subtract(IntervalCollection $subtractIntrevals): IntervalCollection
	{
		$workingIntervals = $this->getArrayCopy();
		// var_dump($workingIntervals);

		// Najít prázdný čas.
		foreach ($workingIntervals as $workingInterval) {
			foreach ($subtractIntrevals as $subtractIntreval) {
				var_dump($workingInterval->subtract($subtractIntreval));
			}
		}


		die;
	}
}
