<?php

namespace Katu\Pdo;

use \Katu\Utils\Cache;

class Table extends \Sexy\Expression {

	public $pdo;
	public $name;

	public function __construct($pdo, $name) {
		$this->pdo  = $pdo;
		$this->name = new Name($name);
	}

	public function __toString() {
		return $this->getSql();
	}

	public function getSql(&$context = []) {
		return implode('.', [new Name($this->pdo->config->database), $this->name]);
	}

	public function getColumns() {
		$columns = [];

		foreach ($this->getColumnNames() as $columnName) {
			$columns[] = new Column($this, $columnName);
		}

		return $columns;
	}

	public function getColumnDescriptions() {
		$table = $this;

		return Cache::getRuntime(['databases', $this->pdo->name, 'tables', 'descriptions', $this->name], function() use($table) {
			$columns = [];
			foreach ($table->pdo->createQuery(" DESCRIBE " . $table)->getResult() as $properties) {
				$columns[$properties['Field']] = $properties;
			}

			return $columns;
		});
	}

	public function getColumnDescription($columnName) {
		$descriptions = $this->getColumnDescriptions();

		return $descriptions[$columnName instanceof Name ? $columnName->name : $columnName];
	}

	public function getColumnNames() {
		return array_values(array_map(function($i) {
			return $i['Field'];
		}, $this->getColumnDescriptions()));
	}

	public function exists() {
		return $this->pdo->tableExists($this->name->name);
	}

	public function rename($name) {
		$sql = " RENAME TABLE " . $this->name . " TO " . $name;
		$res = $this->pdo->createQuery($sql)->getResult();

		Cache::resetRuntime();

		return $res;
	}

	public function delete() {
		$sql = " DROP TABLE " . $this->name;
		$res = $this->pdo->createQuery($sql)->getResult();

		Cache::resetRuntime();

		return $res;
	}

	public function copy($destinationTable, $options = []) {
		// Delete the original table.
		try {
			$destinationTable->delete();
		} catch (\Exception $e) {

		}

		// Create table and copy the data.
		$sql = " CREATE TABLE " . $destinationTable->name . " AS SELECT * FROM " . $this->name;
		$destinationTable->pdo->createQuery($sql)->getResult();

		// Disable NULL.
		if (isset($options['disableNull']) && $options['disableNull']) {

		}

		// Create automatic indices.
		if (isset($options['autoIndices']) && $options['autoIndices']) {
			$indexableColumns = [];

			foreach ($destinationTable->getColumns() as $column) {
				if (in_array($column->getProperties()->type, ['int', 'double', 'char', 'varchar'])) {
					$indexableColumns[] = $column;
				}
			}

			if ($indexableColumns) {
				$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . implode(', ', array_map(function($i) {
					return $i->name;
				}, $indexableColumns)) . "); ";

				try {
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {

				}
			}

			// Create separate indices.
			foreach ($indexableColumns as $indexableColumn) {
				try {
					$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . $indexableColumn->name . ") ";
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {

				}
			}
		}

		// Create custom indices.
		foreach ($options['customIndices'] as $customIndex) {
			try {
				$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . implode(', ', $customIndex) . ") ";
				$destinationTable->pdo->createQuery($sql)->getResult();
			} catch (\Exception $e) {

			}
		}

		Cache::resetRuntime();

		return true;
	}

	public function touch() {
		return \Katu\Utils\Tmp::set(static::getLastUpdatedTmpName(), microtime(true));
	}

	public function getCreateQuery() {
		$sql = " SHOW CREATE TABLE " . $this->name;
		$res = $this->pdo->createQuery($sql)->getResult();

		return $res[0]['Create View'];
	}

	public function getSourceTables() {
		return \Katu\Utils\Cache::get($this->getSourceTablesCacheName(), function($table) {

			$tables = [];

			$sql = " EXPLAIN SELECT * FROM " . $table . " ";
			$res = $table->pdo->createQuery($sql)->getResult()->getArray();
			foreach ($res as $row) {
				if (!preg_match('#^<.+>$#', $row['table'])) {
					$tables[] = $row['table'];
				}
			}

			return array_values(array_filter(array_unique($tables)));

		}, null, $this);
	}

	public function getSourceTablesCacheName() {
		return ['!databases', '!' . $this->pdo->name, '!tables', '!sourceTables', '!' . trim($this->name, '`')];
	}

	public function getUsedInViews() {
		return \Katu\Utils\Cache::get($this->getUsedInViewsCacheName(), function($table) {

			$views = [];

			foreach ($this->pdo->getViewNames() as $viewName) {
				$view = new static($this->pdo, $viewName);
				if (strpos($view->getCreateQuery(), (string) $this->name) !== false) {
					$views[] = $viewName;
				}
			}

			return $views;

		}, null, $this);
	}

	public function getUsedInViewsCacheName() {
		return ['!databases', '!' . $this->pdo->name, '!tables', '!usedInViews', '!' . trim($this->name, '`')];
	}

	public function getTotalRows() {
		$sql = " SELECT COUNT(1) AS total FROM " . $this->name;
		$res = $this->pdo->createQuery($sql)->getResult()->getArray();

		return (int) $res[0]['total'];
	}

	public function getLastUpdatedTmpName() {
		return ['!databases', '!' . $this->pdo->name, '!tables', '!updated', trim($this->name, '`')];
	}

	public function getLastUpdatedTime() {
		return \Katu\Utils\Tmp::get(static::getLastUpdatedTmpName());
	}

}
