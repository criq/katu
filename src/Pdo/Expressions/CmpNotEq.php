<?php

namespace Katu\Pdo\Expressions;

class CmpNotEq extends \Katu\Pdo\Expressions\Cmp {

	public function getSql(&$context = array()) {
		return " NOT ( " . $this->name->getSql($context) . " = " . $this->value->getSql($context) . " ) ";
	}

}
