<?php

namespace Katu\Pdo\Expressions;

class CmpEq extends \Katu\Pdo\Expressions\Cmp {

	public function getSql(&$context = array()) {
		$sql = " ( " . $this->name->getSql($context);
		if (!is_null($this->value)) {
			$sql .= " = " . $this->value->getSql($context);
		}
		$sql .= " ) ";

		return $sql;
	}

}
