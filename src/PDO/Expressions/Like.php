<?php

namespace Katu\PDO\Expressions;

class Like extends Expression {

	public function getWhereConditionSQL($key) {
		return " ( " . $key . " LIKE :" . $key . " ) ";
	}

}
