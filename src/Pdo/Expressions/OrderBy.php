<?php

namespace Katu\Pdo\Expressions;

class OrderBy extends \Katu\Pdo\Expression {

	public $orderBy;
	public $direction;

	public function __construct($orderBy, $direction = 'ASC') {
		$this->orderBy = $orderBy;
		$this->direction = $direction;
	}

	public function getSql(&$context = array()) {
		return $this->orderBy->getSql($context) . " " . $this->direction;
	}

}
