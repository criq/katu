<?php

namespace Jabli\Aids\DB;

class Result {

	public $res;
	public $class;

	public function __construct($res, $class = NULL) {
		$this->res   = $res;
		$this->class = $class;
	}

	public function getOne($class = NULL) {
		if (!$class && $this->class) {
			$class = $this->class;
		}

		return $class::getFromAssoc($this->res->fetch_one());
	}

}
