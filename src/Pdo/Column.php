<?php

namespace Katu\Pdo;

class Column {

	public $table;
	public $name;

	public function __construct($table, $name) {
		$this->table = $table;
		$this->name = $name;
	}

	public function __toString() {
		return $this->getSql();
	}

	public function getSql() {
		return $this->table->getSql() . '.' . $this->name;
	}

	public function getProperties() {
		return new ColumnProperties($this->table->getColumnDescription($this->name));
	}

}
