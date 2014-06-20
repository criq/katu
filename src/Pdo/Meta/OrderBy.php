<?php

namespace Katu\Pdo\Meta;

class OrderBy {

	public $orderBy;

	public function __construct($orderBy) {
		$this->orderBy = (string) $orderBy;
	}

	public function getOrderBy() {
		return $this->orderBy;
	}

}
