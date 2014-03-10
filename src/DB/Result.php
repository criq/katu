<?php

namespace Jabli\DB;

class Result {

	public $res;
	public $class;

	public function __construct($res, $class = NULL) {
		$this->res   = $res;
		$this->class = $class;
	}

	public function getTotal() {
		return (int) $this->res->num_rows;
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
