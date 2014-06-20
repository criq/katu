<?php

namespace Katu\Pdo\Expressions;

class Like extends Expression {

	public function getWhereConditionSQL($pdo, $key) {
		return " ( " . $key . " LIKE :" . $key . " ) ";
	}

}
