<?php

namespace Katu\PDO\Expressions;

class NotEquals extends Expression {

	public function getWhereConditionSQL($key) {
		return " NOT ( " . $key . " = :" . $key . " ) ";
	}

}
