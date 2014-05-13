<?php

namespace Katu\PDO\Meta;

class OrderBy {

	public $orderBy;

	public function __construct($orderBy) {
		$this->orderBy = (string) $orderBy;
	}

	public function getOrderBy() {
		return $this->orderBy;
	}

}
