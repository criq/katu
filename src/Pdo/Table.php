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

	public function getColumns() {
		$columns = [];

		foreach ($this->getColumnNames() as $columnName) {
			$columns[] = new Column($this, $columnName);
		}

		return $columns;
	}

	public function getColumnDescriptions() {
		$columns = [];

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

	public function delete() {
		$sql = " DROP TABLE `" . $this->name . "` ";

		return $this->pdo->createQuery($sql)->getResult();
	}

	public function copy($destinationTable, $options = []) {
		// Delete the original table.
		try {
			$destinationTable->delete();
		} catch (\Exception $e) {

		}

		// Create table and copy the data.
		$sql = " CREATE TABLE `" . $destinationTable->name . "` AS SELECT * FROM `" . $this->name . "`";
		$destinationTable->pdo->createQuery($sql)->getResult();

		// Disable NULL.
		if (isset($options['disableNull']) && $options['disableNull']) {

		}

		if (isset($options['createIndices']) && $options['createIndices']) {
			$indexableColumns = [];

			foreach ($destinationTable->getColumns() as $column) {
				if (in_array($column->getProperties()->type, ['int', 'double', 'char', 'varchar'])) {
					$indexableColumns[] = $column;
				}
			}

			if ($indexableColumns) {
				$sql = " ALTER TABLE `" . $destinationTable->name . "` ADD INDEX (" . implode(', ', array_map(function($i) {
					return "`" . $i->name . "`";
				}, $indexableColumns)) . "); ";

				try {
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {

				}
			}

			// Create separate indices.
			foreach ($indexableColumns as $indexableColumn) {
				try {
					$sql = " ALTER TABLE `" . $destinationTable->name . "` ADD INDEX (`" . $indexableColumn->name . "`) ";
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {

				}
			}
		}

		return true;
	}

	public function saveToFile($fileName) {
		@mkdir(dirname($fileName), 0777, true);
		@chmod(dirname($fileName), 0777);

		$sql = " SELECT * INTO OUTFILE '" . $fileName . "' FROM `" . $this->pdo->name . "`.`" . $this->name . "` ";

		return $this->pdo->createQuery($sql)->getResult();
	}

	public function loadFromFile($fileName) {
		$sql = " LOAD DATA INFILE '" . $fileName . "' INTO TABLE `" . $this->name . "` ";

		return $this->pdo->createQuery($sql)->getResult();
	}

}
