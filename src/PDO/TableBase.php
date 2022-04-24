<?php

namespace Katu\PDO;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

abstract class TableBase extends \Sexy\Expression
{
	public $connection;
	public $name;

	abstract public function getCreateSyntax();

	public function __construct(Connection $connection, $name)
	{
		$this->connection = $connection;
		$this->name = new Name($name);
	}

	public function __toString(): string
	{
		return $this->getSql();
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function getName(): Name
	{
		return $this->name;
	}

	public function getSQL(&$context = []): string
	{
		return implode(".", [new Name($this->getConnection()->getConfig()->database), $this->name]);
	}

	public function getColumns(): array
	{
		$columns = [];
		foreach ($this->getColumnNames() as $columnName) {
			$columns[] = new Column($this, new Name($columnName));
		}

		return $columns;
	}

	public function getColumn(string $column): Column
	{
		return new Column($this, new Name($column));
	}

	public function getColumnDescriptions(): array
	{
		return \Katu\Cache\Runtime::get(new TIdentifier("databases", $this->getConnection()->getName(), "tables", "descriptions", $this->getName()), function () {
			$columns = [];
			foreach ($this->getConnection()->createQuery(" DESCRIBE " . $this->getName())->getResult() as $properties) {
				$columns[$properties["Field"]] = $properties;
			}

			return $columns;
		});
	}

	public function getColumnDescription($columnName)
	{
		$descriptions = $this->getColumnDescriptions();

		return $descriptions[$columnName instanceof Name ? $columnName->name : $columnName];
	}

	public function getColumnNames()
	{
		return array_values(array_map(function ($i) {
			return new Name($i["Field"]);
		}, $this->getColumnDescriptions()));
	}

	public function exists(): bool
	{
		return $this->getConnection()->tableExists($this->name);
	}

	public function rename($name)
	{
		$sql = " RENAME TABLE " . $this->name . " TO " . $name;
		$res = $this->getConnection()->createQuery($sql)->getResult();

		\Katu\Cache\Runtime::clear();

		return $res;
	}

	public function delete()
	{
		$sql = " DROP TABLE " . $this->name;
		$res = $this->getConnection()->createQuery($sql)->getResult();

		\Katu\Cache\Runtime::clear();

		return $res;
	}

	public function copy($destinationTable, $options = [])
	{
		// Delete the original table.
		try {
			$destinationTable->delete();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		$sql = $this->getCreateSyntax();
		if (preg_match("/^CREATE ALGORITHM/", $sql)) {
			// View.
			$sql = " CREATE TABLE " . $destinationTable . " AS SELECT * FROM " . $this;
			$destinationTable->getConnection()->createQuery($sql)->getResult();
		} else {
			// Table.
			$sql = preg_replace_callback("/^CREATE TABLE `([a-z0-9_]+)`/", function ($i) use ($destinationTable) {
				return " CREATE TABLE `" . $destinationTable->name->name . "` ";
			}, $sql);

			if ($options["createSqlSanitizeCallback"] ?? null) {
				$callback = $options["createSqlSanitizeCallback"];
				$sql = $callback($sql);
			}

			$destinationTable->getConnection()->createQuery($sql)->getResult();

			// Create table and copy the data.
			$sql = " INSERT " . (($options["insertIgnore"] ?? null) ? " IGNORE " : null) . " INTO " . $destinationTable . " SELECT * FROM " . $this;
			$destinationTable->getConnection()->createQuery($sql)->getResult();
		}

		// Disable NULL.
		if (isset($options["disableNull"]) && $options["disableNull"]) {
		}

		// Create automatic indices.
		if (isset($options["autoIndices"]) && $options["autoIndices"]) {
			$indexableColumns = [];

			foreach ($destinationTable->getColumns() as $column) {
				if (in_array($column->getProperties()->type, [
					"date", "datetime", "timestamp", "year",
					"tinyint", "smallint", "mediumint", "int", "bigint",
					"float", "double", "real", "decimal",
					"char", "varchar",
				])) {
					$indexableColumns[] = $column;
				}
			}

			// Composite index.
			if ($indexableColumns && $options["compositeIndex"]) {
				$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . implode(", ", array_map(function ($i) {
					return $i->name;
				}, $indexableColumns)) . "); ";

				try {
					$destinationTable->getConnection()->createQuery($sql)->getResult();
				} catch (\Throwable $e) {
					// Nevermind.
				}
			}

			// Create separate indices.
			foreach ($indexableColumns as $indexableColumn) {
				try {
					$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . $indexableColumn->name . ") ";
					$destinationTable->getConnection()->createQuery($sql)->getResult();
				} catch (\Throwable $e) {
					// Nevermind.
				}
			}
		}

		// Create custom indices.
		foreach (($options["customIndices"] ?? []) as $customIndex) {
			try {
				$sql = " ALTER TABLE " . $destinationTable->name . " ADD INDEX (" . implode(", ", $customIndex) . ") ";
				$destinationTable->getConnection()->createQuery($sql)->getResult();
			} catch (\Throwable $e) {
				// Nevermind.
			}
		}

		\Katu\Cache\Runtime::clear();

		return true;
	}

	public function getUsedInViews()
	{
		return \Katu\Cache\General::get($this->getUsedInViewsCacheIdentifier(), new Timeout("1 day"), function ($table) {
			$views = [];

			foreach ($this->getConnection()->getViewNames() as $viewName) {
				$view = new static($this->getConnection(), $viewName);
				if (strpos($view->getCreateSyntax(), (string) $this->name) !== false && $viewName != $this->name->name) {
					$views[] = $viewName;
				}
			}

			return $views;
		}, $this);
	}

	public function getUsedInViewsCacheIdentifier(): TIdentifier
	{
		return new TIdentifier("databases", $this->getConnection()->getName(), "tables", "usedInViews", $this->name);
	}

	public function getTotalUsage(Timeout $timeout)
	{
		return \Katu\Cache\General::get($this->getUsedInViewsCacheIdentifier(), $timeout, function ($table) {
			$stopwatch = new \Katu\Tools\Profiler\Stopwatch;

			$sql = " SELECT COUNT(1) AS total FROM " . $table->name;
			$res = $table->getConnection()->createQuery($sql)->getResult()->getItems();

			return [
				"rows" => (int) $res[0]["total"],
				"duration" => $stopwatch->getDuration(),
			];
		}, $this);
	}

	public function getTotalUsageCacheIdentifier(): TIdentifier
	{
		return new TIdentifier("databases", $this->getConnection()->getName(), "tables", "totalRows", $this->name);
	}

	public function getLastUpdatedTemporaryFile()
	{
		return new \Katu\Files\Temporary("databases", $this->getConnection()->getName(), "tables", "updated", $this->name);
	}

	public function getPrimaryKeyColumnName(): ?string
	{
		$cacheIdentifier = new TIdentifier("databases", $this->getConnection()->getName(), "tables", "idColumn", $this->getName()->getName());

		return \Katu\Cache\Runtime::get($cacheIdentifier, function () use ($cacheIdentifier) {
			return \Katu\Cache\General::get($cacheIdentifier, new Timeout("10 minutes"), function () {
				foreach ($this->getConnection()->createQuery(" DESCRIBE " . $this)->getResult() as $row) {
					if (isset($row["Key"]) && $row["Key"] == "PRI") {
						return $row["Field"];
					}
				}

				return null;
			});
		});
	}

	public function getPrimaryKeyColumn(): ?Column
	{
		try {
			return $this->getColumn($this->getPrimaryKeyColumnName());
		} catch (\Throwable $e) {
			return null;
		}
	}
}
