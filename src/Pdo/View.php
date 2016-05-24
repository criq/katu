<?php

namespace Katu\Pdo;

use \Katu\Utils\Cache;

class View extends Table {

	public function getCreateSyntax() {
		$sql = " SHOW CREATE TABLE " . $this->name;
		$res = $this->pdo->createQuery($sql)->getResult();

		return $res[0]['Create View'];
	}

	public function getSourceTables() {
		$tableNames = \Katu\Utils\Cache::get($this->getSourceTablesCacheName(), function($table) {

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

		$tables = [];
		foreach ($tableNames as $tableName) {
			$tables[] = new Table($this->pdo, $tableName);
		}

		return $tables;
	}

	public function getSourceMaterializedViewNames() {
		if (preg_match_all('#`(mv_[a-z0-9_]+)`#', $this->getCreateSyntax(), $matches)) {
			return array_values(array_unique($matches[1]));
		}

		return false;
	}

	public function getSourceViewsInMaterializedViews() {
		$views = [];

		foreach (array_filter((array) $this->getSourceMaterializedViewNames()) as $tableName) {
			$views[] = new static($this->pdo, preg_replace('#^mv_#', 'view_', $tableName));
		}

		return $views;
	}

	public function getSourceTablesCacheName() {
		return ['!databases', '!' . $this->pdo->name, '!views', '!sourceTables', '!' . trim($this->name, '`')];
	}

	public function getModelNames() {
		$modelNames = [];

		foreach (\Katu\ViewModel::getAllViewModelNames() as $class) {
			$class = '\\' . ltrim($class, '\\');
			if ($class::TABLE == $this->name->name) {
				$modelNames[] = $class;
			}
		}

		return $modelNames;
	}

}
