<?php

namespace Katu\PDO\Expressions;

class GreaterThan extends Expression {

	public function getWhereConditionSQL($key) {
		return " ( " . $key . " > :" . $key . " ) ";
	}

}
