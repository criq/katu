<?php

namespace Katu\PDO\Results;

use \PDO;

class ClassResult extends PaginatedResult {

	public function __construct($pdo, $statement, $page, $class) {
		parent::__construct($pdo, $statement, $page);

		$this->class = $class;
	}

	// Objects.
	public function getOne($class = NULL) {
		if (!$class && $this->class) {
			$class = $this->class;
		}

		$object = $class::getFromAssoc($this->statement->fetchObject($class));
		if ($object) {
			$object->save();
		}

		return $object;
	}

	public function getObjects($class = NULL) {
		if (!$class && $this->class) {
			$class = $this->class;
		}

		return $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
	}

}
