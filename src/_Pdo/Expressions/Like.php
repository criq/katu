<?php

namespace Katu\PDO\Expressions;

class Like extends Expression {

	public function getWhereConditionSQL($pdo, $key) {
		return " ( " . $key . " LIKE :" . $key . " ) ";
	}

}
