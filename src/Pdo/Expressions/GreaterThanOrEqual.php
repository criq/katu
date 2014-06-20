<?php

namespace Katu\Pdo\Expressions;

class GreaterThanOrEqual extends Expression {

	public function getWhereConditionSQL($pdo, $key) {
		return " ( " . $key . " >= :" . $key . " ) ";
	}

}
