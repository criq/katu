<?php

namespace Katu\Pdo;

abstract class Expression {

	abstract public function getSql(&$context = array());

}
