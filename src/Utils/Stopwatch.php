<?php

namespace Katu\Utils;

class Stopwatch {

	public $factorNano  = 1000000000;
	public $factorMicro = 1000000;
	public $factorMilli = 1000;
	public $factorBase  = 1;

	public $nanoStart;

	public function __construct() {
		$this->nanoStart = (float) (DateTime::getMicrotime() * $this->factorNano);
	}

	public function getNanoValues() {
		return [$this->nanoStart, (float) (DateTime::getMicrotime() * $this->factorNano)];
	}

	public function getNanoDuration() {
		$values = $this->getNanoValues();

		return (float) ((max($values) - min($values)));
	}

	public function getMicroDuration() {
		return (float) ($this->getNanoDuration() * ($this->factorMicro / $this->factorNano));
	}

	public function getMilliDuration() {
		return (float) ($this->getNanoDuration() * ($this->factorMilli / $this->factorNano));
	}

	public function getDuration() {
		return (float) ($this->getNanoDuration() * ($this->factorBase / $this->factorNano));
	}

}
