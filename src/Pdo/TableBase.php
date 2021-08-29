<?php

namespace Katu\Pdo;

use \Katu\Utils\Cache;

class TableBase extends \Sexy\Expression {

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

	public function getColumn($columnName) {
		return new Column($this, $columnName);
	}

	public function getColumnDescriptions() {
		$table = $this;

		return Cache::getFromMemory(['databases', $this->pdo->name, 'tables', 'descriptions', $this->name], function() use($table) {

			$columns = [];
			foreach ($table->pdo->createQuery(" DESCRIBE " . $table->name)->getResult() as $properties) {
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
		$sql = " RENAME TABLE " . $this->name->name . " TO " . $name;
		$res = $this->pdo->createQuery($sql)->getResult();

		Cache::resetRuntime();

		return $res;
	}

	public function delete() {
		$sql = " DROP TABLE " . $this->name->name;
		$res = $this->pdo->createQuery($sql)->getResult();

		Cache::resetRuntime();

		return $res;
	}

	public function copy($destinationTable, $options = []) {
		// Delete the original table.
		try {
			$destinationTable->delete();
		} catch (\Exception $e) {
			// Nevermind.
		}

		$sql = $this->getCreateSyntax();
		if (preg_match('/^CREATE ALGORITHM/', $sql)) {

			// View.
			$sql = " CREATE TABLE " . $destinationTable . " AS SELECT * FROM " . $this;
			$destinationTable->pdo->createQuery($sql)->getResult();

		} else {

			// Table.
			$sql = preg_replace_callback('/^CREATE TABLE `([a-z0-9_]+)`/', function($i) use($destinationTable) {
				return "CREATE TABLE `" . $destinationTable->name->name . "`";
			}, $sql);
			$destinationTable->pdo->createQuery($sql)->getResult();

			// Create table and copy the data.
			$sql = " INSERT INTO " . $destinationTable . " SELECT * FROM " . $this;
			$destinationTable->pdo->createQuery($sql)->getResult();

		}

		// Disable NULL.
		if (isset($options['disableNull']) && $options['disableNull']) {

		}

		// Create automatic indices.
		if (isset($options['autoIndices']) && $options['autoIndices']) {
			$indexableColumns = [];

			foreach ($destinationTable->getColumns() as $column) {
				if (in_array($column->getProperties()->type, [
					'date', 'datetime', 'timestamp', 'year',
					'tinyint', 'smallint', 'mediumint', 'int', 'bigint',
					'float', 'double', 'real', 'decimal',
					'char', 'varchar',
				])) {
					$indexableColumns[] = $column;
				}
			}

			// Composite index.
			if ($indexableColumns && $options['compositeIndex']) {
				$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . implode(', ', array_map(function($i) {
					return $i->name;
				}, $indexableColumns)) . "); ";

				try {
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {
					// Nevermind.
				}
			}

			// Create separate indices.
			foreach ($indexableColumns as $indexableColumn) {
				try {
					$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . $indexableColumn->name . ") ";
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {
					// Nevermind.
				}
			}
		}

		// Create custom indices.
		if (isset($options['customIndices'])) {
			foreach ($options['customIndices'] as $customIndex) {
				try {
					$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . implode(', ', $customIndex) . ") ";
					$destinationTable->pdo->createQuery($sql)->getResult();
				} catch (\Exception $e) {
					// Nevermind.
				}
			}
		}

		Cache::resetRuntime();

		return true;
	}

	public function getUsedInViews() {
		return \Katu\Utils\Cache::get($this->getUsedInViewsCacheName(), function($table) {

			$views = [];

			foreach ($this->pdo->getViewNames() as $viewName) {
				$view = new static($this->pdo, $viewName);
				if (strpos($view->getCreateSyntax(), (string) $this->name) !== false && $viewName != $this->name->name) {
					$views[] = $viewName;
				}
			}

			return $views;

		}, null, $this);
	}

	public function getUsedInViewsCacheName() {
		return ['!databases', '!' . $this->pdo->name, '!tables', '!usedInViews', '!' . trim($this->name, '`')];
	}

	public function getTotalUsage($timeout = null) {
		return \Katu\Utils\Cache::get($this->getTotalUsageCacheName(), function($table) {

			$stopwatch = new \Katu\Utils\Stopwatch();

			$sql = " SELECT COUNT(1) AS total FROM " . $table->name;
			$res = $table->pdo->createQuery($sql)->getResult()->getArray();

			return [
				'rows' => (int) $res[0]['total'],
				'duration' => $stopwatch->getDuration(),
			];

		}, $timeout, $this);
	}

	public function getTotalUsageCacheName() {
		return ['!databases', '!' . $this->pdo->name, '!tables', '!totalRows', '!' . trim($this->name, '`')];
	}

	public function getLastUpdatedTmpName() {
		return ['!databases', '!' . $this->pdo->name, '!tables', '!updated', trim($this->name, '`')];
	}

	public function getPdo() {
		return $this->pdo;
	}

}
