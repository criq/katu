<?php

namespace Katu\PDO\Results;

use \PDO;
use \Katu\PDO\Meta\Page;

class ClassResult extends PaginatedResult {

	const DEFAULT_PAGE    = 1;
	const DEFAULT_PERPAGE = 100;

	public function __construct($pdo, $statement, $page, $class) {
		// Set default page if empty.
		if (!$page) {
			$page = new Page(static::DEFAULT_PAGE, static::DEFAULT_PERPAGE);
		}

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
