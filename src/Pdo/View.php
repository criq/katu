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

	public function getSourceTablesCacheName() {
		return ['!databases', '!' . $this->pdo->name, '!views', '!sourceTables', '!' . trim($this->name, '`')];
	}

}
