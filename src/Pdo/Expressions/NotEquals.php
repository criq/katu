<?php

namespace Katu\Pdo\Expressions;

class NotEquals extends Expression {

	public function getWhereConditionSQL($pdo, $key) {
		return " NOT ( " . $key . " = :" . $key . " ) ";
	}

}
