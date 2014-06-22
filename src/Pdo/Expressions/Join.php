<?php

namespace Katu\Pdo\Expressions;

class Join extends \Katu\Pdo\Expression {

	public $join;
	public $conditions;
	public $alias;

	public function __construct($join, $conditions, $alias = NULL) {
		$this->join = $join;
		$this->conditions = $conditions;
		$this->alias = $alias;
	}

	public function getSql(&$context = array()) {
		return " JOIN " . $this->join->getSql($context) . " ON ( " . $this->conditions->getSql($context) . " ) " . ($this->alias ? " AS " . $this->alias : NULL);
	}

}
