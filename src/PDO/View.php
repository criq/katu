<?php

namespace Katu\PDO;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class View extends Table
{
	public function getCreateSyntax()
	{
		$sql = " SHOW CREATE TABLE " . $this->getName();
		$res = $this->getConnection()->createQuery($sql)->getResult();

		return $res[0]['Create View'];
	}

	public function getSourceTables()
	{
		$tableNames = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout('1 day'), function ($table) {
			$tableNames = [];

			$sql = " EXPLAIN SELECT * FROM " . $table . " ";
			$res = $table->getConnection()->createQuery($sql)->getResult()->getItems();
			foreach ($res as $row) {
				if (!preg_match('/^<.+>$/', $row['table'])) {
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

	public function getSourceMaterializedViewNames()
	{
		if (preg_match_all('/`(mv_[a-z0-9_]+)`/', $this->getCreateSyntax(), $matches)) {
			return array_values(array_unique($matches[1]));
		}

		return false;
	}

	public function getSourceViewsInMaterializedViews()
	{
		$views = [];

		foreach (array_filter((array) $this->getSourceMaterializedViewNames()) as $tableName) {
			$views[] = new static($this->getConnection(), new Name(preg_replace('/^mv_/', 'view_', $tableName)));
		}

		return $views;
	}

	public function getModels() : array
	{
		$models = [];
		foreach (\Katu\Models\View::getAllViewClasses() as $class) {
			if ($class->getName()::TABLE == $this->getName()->getName()) {
				$models[] = $class;
			}
		}

		return $models;
	}
}
