<?php

namespace Katu\Pdo\Expressions;

class CmpLike extends \Katu\Pdo\Expressions\Cmp {

	public function getSql(&$context = array()) {
		return " ( " . $this->name->getSql($context) . " LIKE " . $this->value->getSql($context) . " ) ";
	}

}
