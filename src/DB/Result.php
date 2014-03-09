<?php

namespace Jabli\Utils\DB;

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

		$object = $class::getFromAssoc($this->res->fetch_one());
		if ($object) {
			$object->save();
		}

		return $object;
	}

}
