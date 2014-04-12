<?php

namespace Katu\DB;

class Result {

	const ORDERBY = 'order_by';
	const PAGE    = 'page';

	public $res;
	public $class;

	public function __construct($res, $class = NULL) {
		$this->res   = $res;
		$this->class = $class;
	}

	static function get($res, $class = NULL) {
		return new self($res, $class);
	}

	public function getTotal() {
		return (int) $this->res->num_rows;
	}

	public function getPagination() {
		return new \Katu\Types\Pagination($this->res->found_rows, $this->res->limit, $this->res->page);
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

	public function getObjects($class = NULL) {
		if (!$class && $this->class) {
			$class = $this->class;
		}

		$objects = array();
		foreach ($this->res->fetch_all() as $item) {
			$objects[] = $class::getFromAssoc($item);
		}

		return $objects;
	}

	public function getArray() {
		return (array) $this->res->fetch_all();
	}

	public function getProperty($property) {
		return (array) array_map(function($i) use($property){
			return $i[$property];
		}, $this->getArray());
	}

}
