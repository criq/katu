<?php

namespace Katu\Pdo\Expressions;

class GreaterThan extends Expression {

	public function getWhereConditionSQL($pdo, $key) {
		return " ( " . $key . " > :" . $key . " ) ";
	}

}
