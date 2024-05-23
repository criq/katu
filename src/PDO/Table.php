<?php

namespace Katu\PDO;

use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Options\OptionCollection;
use Katu\Types\TIdentifier;

class Table extends \Sexy\Expression
{
	public $connection;
	public $name;

	public function __construct(Connection $connection, Name $name)
	{
		$this->setConnection($connection);
		$this->setName($name);
	}

	public function __toString(): string
	{
		return $this->getSql();
	}

	public function setConnection(Connection $value): Table
	{
		$this->connection = $value;

		return $this;
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function setName(Name $value): Table
	{
		$this->name = $value;

		return $this;
	}

	public function getName(): Name
	{
		return $this->name;
	}

	public function getColumnDescriptions(): ColumnDescriptionCollection
	{
		return \Katu\Cache\Runtime::get(new TIdentifier("databases", $this->getConnection()->getName(), "tables", "descriptions", $this->getName()->getPlain()), function () {
			$res = new ColumnDescriptionCollection;
			$sql = " DESCRIBE {$this->getName()} ";
			foreach ($this->getConnection()->createQuery($sql)->getResult() as $description) {
				$res[] = ColumnDescription::createFromResponse($description);
			}

			return $res;
		});
	}

	public function getColumnNames(): NameCollection
	{
		$res = new NameCollection;
		foreach ($this->getColumnDescriptions() as $columnDescription) {
			$res[] = new Name($columnDescription->getName());
		}

		return $res;
 	}

	public function getColumns(): ColumnCollection
	{
		$res = new ColumnCollection;
		foreach ($this->getColumnNames() as $columnName) {
			$res[] = new Column($this, $columnName);
		}

		return $res;
	}

	public function getColumn($name): Column
	{
		return new Column($this, Name::createFromInput($name));
	}

	public function getPrimaryKeyColumn(): ?Column
	{
		return \Katu\Cache\Runtime::get(new TIdentifier("databases", $this->getConnection()->getName(), "tables", "idColumn", $this->getName()->getPlain()), function () {
			foreach ($this->getConnection()->createQuery(" DESCRIBE " . $this)->getResult() as $row) {
				if (($row["Key"] ?? null) == "PRI") {
					return new Column($this, new Name($row["Field"]));
				}
			}

			return null;
		});
	}

	public function getMaxTableNameLength(): int
	{
		return 64;
	}

	public function exists(): bool
	{
		return $this->getConnection()->tableExists($this->name);
	}

	public function rename(string $name)
	{
		$sql = " RENAME TABLE {$this->getName()} TO {$name}";
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

	public function copy(Table $destinationTable, ?OptionCollection $options = null): bool
	{
		$options = (new OptionCollection)->getMergedWith($options);

		// Delete the original table.
		try {
			$destinationTable->delete();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		$sql = $this->getCreateSyntax();
		if (preg_match("/^CREATE ALGORITHM/", $sql)) {
			// View.
			$sql = " CREATE TABLE {$destinationTable->getName()} AS SELECT * FROM {$this->getName()} ";
			$destinationTable->getConnection()->createQuery($sql)->getResult();
		} else {
			// Table.
			$sql = preg_replace_callback("/^CREATE TABLE (`[a-z0-9_]+`)/", function () use ($destinationTable) {
				return " CREATE TABLE {$destinationTable->getName()} ";
			}, $sql);

			if ($options->getValue("CREATE_SQL_SANITIZE_CALLBACK")) {
				$callback = $options->getValue("CREATE_SQL_SANITIZE_CALLBACK");
				$sql = $callback($sql);
			}

			$destinationTable->getConnection()->createQuery($sql)->getResult();

			// Create table and copy the data.
			$sql = " INSERT " . ($options->getValue("INSERT_IGNORE") ? " IGNORE " : null) . " INTO {$destinationTable->getName()} SELECT * FROM {$this->getName()} ";
			$destinationTable->getConnection()->createQuery($sql)->getResult();
		}

		// Create automatic indices.
		if ($options->getValue("AUTO_INDICES")) {
			$indexableColumns = [];

			foreach ($destinationTable->getColumns() as $column) {
				if (in_array($column->getDescription()->type, [
					"bigint",
					"char",
					"date",
					"datetime",
					"decimal",
					"double",
					"float",
					"int",
					"mediumint",
					"real",
					"smallint",
					"timestamp",
					"tinyint",
					"varchar",
					"year",
				])) {
					$indexableColumns[] = $column;
				}
			}

			// Composite index.
			if ($indexableColumns && $options->getValue("COMPOSITE_INDEX")) {
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
					$sql = " ALTER TABLE {$destinationTable->getName()} ADD INDEX ({$indexableColumn->getName()}) ";
					$destinationTable->getConnection()->createQuery($sql)->getResult();
				} catch (\Throwable $e) {
					// Nevermind.
				}
			}
		}

		// Create custom indices.
		foreach (($options["customIndices"] ?? []) as $customIndex) {
			try {
				$sql = " ALTER TABLE {$destinationTable->getName()} ADD INDEX (" . implode(", ", $customIndex) . ") ";
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
				if (strpos($view->getCreateSyntax(), (string) $this->name) !== false && $viewName != $this->getName()->getPlain()) {
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

	public function getLastUpdatedTemporaryFile(): \Katu\Files\File
	{
		return new \Katu\Files\Temporary("databases", $this->getConnection()->getName(), "tables", "updated", $this->getName()->getPlain());
	}

	public function getSQL(&$context = []): string
	{
		return implode(".", [new Name($this->getConnection()->getConfig()->getDatabase()), $this->getName()]);
	}

	public function getCreateSyntax(): string
	{
		$sql = " SHOW CREATE TABLE " . $this->name;
		$res = $this->getConnection()->createQuery($sql)->getResult();

		if (isset($res[0]["View"])) {
			return $res[0]["Create View"];
		}

		return $res[0]["Create Table"];
	}

	public function touch(): bool
	{
		$file = $this->getLastUpdatedTemporaryFile();
		$file->touch();

		return true;
	}

	public function getLastUpdatedDateTime()
	{
		$file = $this->getLastUpdatedTemporaryFile();

		return $file->getModifiedTime();
	}

	public function getType(): string
	{
		$sql = " SHOW CREATE TABLE {$this->getName()}";
		$res = $this->getConnection()->createQuery($sql)->getResult();

		if (isset($res[0]["View"])) {
			return "VIEW";
		}

		return "TABLE";
	}

	public function isTable(): bool
	{
		return $this->getType() == "table";
	}

	public function isView(): bool
	{
		return $this->getType() == "view";
	}

	public function getChecksum()
	{
		$sql = " CHECKSUM TABLE " . $this->getName();
		$res = $this->getConnection()->createQuery($sql)->getResult()[0]["Checksum"];

		return (int)$res;
	}
}
