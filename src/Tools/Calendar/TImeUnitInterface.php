<?php

namespace Katu\Tools\Calendar;

interface TimeUnitInterface
{
	public function getTime(): Time;
	public function getStart(): Time;
	public function getEnd(): Time;
	public function getInterval(): Interval;
}
