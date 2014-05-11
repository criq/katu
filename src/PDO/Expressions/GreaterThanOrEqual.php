<?php

namespace Katu\PDO\Expressions;

class GreaterThanOrEqual extends Expression {

	public function getWhereConditionSQL($key) {
		return " ( " . $key . " >= :" . $key . " ) ";
	}

}
