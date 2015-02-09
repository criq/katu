<?php

namespace Katu\Pdo;

class Table extends \Sexy\Expression {

	public $pdo;
	public $name;

	public function __construct($pdo, $name) {
		$this->pdo  = $pdo;
		$this->name = $name;
	}

	public function __toString() {
		return $this->getSql();
	}

	public function getSql(&$context = []) {
		return implode('.', ["`" . $this->pdo->config->database . "`", "`" . $this->name . "`"]);
	}

	public function getColumnDescriptions() {
		$columns = array();

		foreach ($this->pdo->createQuery(" DESCRIBE " . $this->name)->getResult() as $properties) {
			$columns[$properties['Field']] = $properties;
		}

		return $columns;
	}

	public function getColumnDescription($columnName) {
		$descriptions = $this->getColumnDescriptions();

		return $descriptions[$columnName];
	}

	public function getColumnNames() {
		return array_values(array_map(function($i) {
			return $i['Field'];
		}, $this->getColumnDescriptions()));
	}

}
