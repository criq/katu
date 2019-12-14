<?php

namespace Katu\PDO;

class View extends Table {

	public function getCreateSyntax() {
		$sql = " SHOW CREATE TABLE " . $this->getName();
		$res = $this->getConnection()->createQuery($sql)->getResult();

		return $res[0]['Create View'];
	}

	public function getSourceTables() {
		$tableNames = \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], 86400, function($table) {

			$tableNames = [];

			$sql = " EXPLAIN SELECT * FROM " . $table . " ";
			$res = $table->getConnection()->createQuery($sql)->getResult()->getArray();
			foreach ($res as $row) {
				if (!preg_match('#^<.+>$#', $row['table'])) {
					$tableNames[] = new \Katu\PDO\Name($row['table']);
				}
			}

			return array_values(array_filter(array_unique($tableNames)));

		}, $this);

		$tables = [];
		foreach ($tableNames as $tableName) {
			$tables[] = new Table($this->getConnection(), $tableName);
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
			$views[] = new static($this->getConnection(), preg_replace('/^mv_/', 'view_', $tableName));
		}

		return $views;
	}

	public function getModelNames() {
		$modelNames = [];

		foreach (\Katu\Models\View::getAllViewClassNames() as $class) {
			$class = '\\' . ltrim($class, '\\');
			if ($class::TABLE == $this->getName()->getName()) {
				$modelNames[] = $class;
			}
		}

		return $modelNames;
	}

}
