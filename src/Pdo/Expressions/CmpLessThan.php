<?php

namespace Katu\Pdo\Expressions;

class CmpLessThan extends \Katu\Pdo\Expressions\Cmp {

	public function getSql(&$context = array()) {
		return " ( " . $this->name->getSql($context) . " < " . $this->value->getSql($context) . " ) ";
	}

}
