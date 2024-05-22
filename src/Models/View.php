<?php

namespace Katu\Models;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;

abstract class View extends Base
{
	const AUTO_INDICES = true;
	const CACHE = true;
	const CACHE_ADVANCE = .75;
	const CACHE_DATETIME_FORMAT = "YmdHis";
	const CACHE_ON_UPDATE = true;
	const CACHE_TIMEOUT = 86400;
	const COMPOSITE_INDEX = true;
	const CUSTOM_INDICES = "";
	const MATERIALIZE = false;
	const MATERIALIZE_ADVANCE = 1;
	const MATERIALIZE_HOURS = "";
	const MATERIALIZE_TIMEOUT = 86400;
	const MAX_NAME_LENGTH = 64;
	const PREFIX_CACHE = "_cache";
	const SEPARATOR = "_";
	const TIMEOUT = 3600;
	const TMP_LENGTH = 8;

	protected static $cachedTableNames;

	public static function getTableClass(): TClass
	{
		return new TClass("Katu\PDO\Table");
	}

	public static function getViewClass(): TClass
	{
		return new TClass("Katu\PDO\View");
	}

	public static function getColumnClass(): TClass
	{
		return new TClass("Katu\PDO\Column");
	}

	public static function getTable(): \Katu\PDO\Table
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__, static::class), function () {
			return static::isCached() ? static::getCachedTable() : static::getView();
		});
	}

	public static function getTableName(): \Katu\PDO\Name
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__, static::class), function () {
			return static::isCached() ? static::getCachedTableName() : static::getViewName();
		});
	}

	public static function getView()
	{
		$viewClass = static::getViewClass()->getName();

		return new $viewClass(static::getConnection(), static::getViewName());
	}

	public static function getViewName(): \Katu\PDO\Name
	{
		return new \Katu\PDO\Name(static::TABLE);
	}

	public static function getColumn($name, $options = []): \Katu\PDO\Column
	{
		if (isset($options["cache"]) && $options["cache"] === false) {
			$table = static::getView();
		} else {
			$table = static::getTable();
		}

		$columnClass = static::getColumnClass()->getName();

		return new $columnClass($table, new \Katu\PDO\Name($name));
	}

	public static function getViewColumn($name, $options = [])
	{
		$options["cache"] = false;

		return static::getColumn($name, $options);
	}

	public static function getCachedTableNameBase()
	{
		return implode(static::SEPARATOR, [
			static::PREFIX_CACHE,
			static::getViewName()->getPlain(),
		]);
	}

	public static function getMetaStringLength()
	{
		$str = implode([
			static::PREFIX_CACHE,
			static::SEPARATOR,
			static::SEPARATOR,
			(new \Katu\Tools\Calendar\Time)->format(static::CACHE_DATETIME_FORMAT),
			static::SEPARATOR,
			\Katu\Tools\Random\Generator::getIdString(static::TMP_LENGTH),
		]);

		return strlen($str);
	}

	public static function getCachedTableShortNameBase()
	{
		$hash = substr(hash("sha1", static::getViewName()->getPlain()), 0, 8);

		$str = implode(static::SEPARATOR, array_merge([static::PREFIX_CACHE], array_map(function ($i) {
			return substr($i, 0, 3);
		}, explode("_", static::getViewName()->getPlain()))));

		$maxLength = static::MAX_NAME_LENGTH - static::getMetaStringLength() - strlen($hash) + strlen(static::PREFIX_CACHE);
		$str = substr($str, 0, $maxLength);

		$str = implode(static::SEPARATOR, [
			$str,
			$hash,
		]);

		return $str;
	}

	public static function getCachedTable()
	{
		try {
			static::cacheIfExpired();

			// Try cached table name.
			$tableName = static::getCachedTableName();
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			$tableName = static::getViewName();
		}

		$tableClass = static::getTableClass()->getName();

		return new $tableClass(static::getConnection(), $tableName);
	}

	public static function getCachedTablesSql()
	{
		$sql = " SELECT *
			FROM information_schema.tables
			WHERE TABLE_SCHEMA = :tableSchema
			AND TABLE_NAME REGEXP :tableRegexp
			ORDER BY TABLE_NAME DESC ";

		return $sql;
	}

	public static function getCachedTableNameRegexp()
	{
		return implode(static::SEPARATOR, [
			"(" . implode("|", [static::getCachedTableNameBase(), static::getCachedTableShortNameBase()]) . ")",
			"(?<datetime>[0-9]{" . strlen((new \Katu\Tools\Calendar\Time)->format(static::CACHE_DATETIME_FORMAT)) . "})",
			"([" . \Katu\Tools\Random\Generator::IDSTRING . "]{" . static::TMP_LENGTH . "})",
		]);
	}

	public static function getCachedTablesQuery()
	{
		$sql = static::getCachedTablesSql();

		$query = static::getConnection()->createQuery($sql, [
			"tableSchema" => static::getConnection()->getConfig()->database,
			"tableRegexp" => strtr(static::getCachedTableNameRegexp(), [
				"?<datetime>" => null,
			]),
		]);

		return $query;
	}

	public static function getCachedTableName()
	{
		$className = static::getClass()->getName();

		if (static::$cachedTableNames[$className] ?? null) {
			return static::$cachedTableNames[$className];
		}

		$array = static::getCachedTablesQuery()->getResult();

		if ($array[0]["TABLE_NAME"] ?? null) {
			static::$cachedTableNames[$className] = new \Katu\PDO\Name($array[0]["TABLE_NAME"]);
			return static::$cachedTableNames[$className];
		}

		if (static::cache()) {
			return static::getCachedTableName();
		}

		return static::getViewName();
	}

	public static function generateCachedTable()
	{
		$tableClass = static::getTableClass()->getName();

		return new $tableClass(static::getConnection(), static::generateCachedTableName());
	}

	public static function generateCachedTableName()
	{
		$name = implode(static::SEPARATOR, array_merge([static::getCachedTableNameBase()], [
			(new \Katu\Tools\Calendar\Time)->format(static::CACHE_DATETIME_FORMAT),
			\Katu\Tools\Random\Generator::getIdString(static::TMP_LENGTH),
		]));

		if (strlen($name) > static::MAX_NAME_LENGTH) {
			$name = implode(static::SEPARATOR, array_merge([static::getCachedTableShortNameBase()], [
				(new \Katu\Tools\Calendar\Time)->format(static::CACHE_DATETIME_FORMAT),
				\Katu\Tools\Random\Generator::getIdString(static::TMP_LENGTH),
			]));
		}

		return new \Katu\PDO\Name($name);
	}

	public static function isCached()
	{
		return static::CACHE;
	}

	public static function isMaterialized()
	{
		return static::MATERIALIZE;
	}

	public static function cachedTableExists()
	{
		$query = static::getCachedTablesQuery();
		$array = $query->getResult()->getItems();

		return (bool)($array[0] ?? null);
	}

	public static function materializedTableExists()
	{
		return in_array(static::getMaterializedTableName(), static::getConnection()->getTableNames());
	}

	public static function cacheHasUpdatedTables()
	{
		if (static::CACHE_ON_UPDATE) {
			$sourceTables = static::getView()->getSourceTables();
			foreach ($sourceTables as $sourceTable) {
				if (!$sourceTable->exists()) {
					continue;
				}

				$lastUpdatedTime = $sourceTable->getLastUpdatedDateTime();
				$lastCachedDateTime = static::getLastCachedDateTime();
				if ($lastUpdatedTime && $lastCachedDateTime && $lastUpdatedTime->getTimestamp() > $lastCachedDateTime->getTimestamp()) {
					return true;
				}
			}
		}

		return false;
	}

	public static function getCacheAge()
	{
		$lastCachedDateTime = static::getLastCachedDateTime();

		return time() - ($lastCachedDateTime ? $lastCachedDateTime->getTimestamp() : 0);
	}

	public static function getMaterializeAge()
	{
		return time() - static::getLastMaterializedTime();
	}

	public static function getCacheExpiryRatio(): float
	{
		return static::getCacheAge() / static::CACHE_TIMEOUT;
	}

	public static function getMaterializeExpiryRatio(): float
	{
		return static::getMaterializeAge() / static::MATERIALIZE_TIMEOUT;
	}

	public static function isCacheExpired($expiryRatio = 1): bool
	{
		if (!static::isCached()) {
			return false;
		}

		if (!static::cachedTableExists()) {
			return true;
		}

		if (static::getCacheExpiryRatio() >= $expiryRatio) {
			return true;
		}

		if (static::cacheHasUpdatedTables()) {
			return true;
		}

		return false;
	}

	public static function isCacheExpiredAdvance(): bool
	{
		return static::isCacheExpired(static::CACHE_ADVANCE);
	}

	public static function isMaterializeExpired($expiryRatio = 1): bool
	{
		if (!static::isMaterialized()) {
			return false;
		}

		if (!static::materializedTableExists()) {
			return true;
		}

		if (static::getMaterializeExpiryRatio() >= $expiryRatio) {
			return true;
		}

		return false;
	}

	public static function isMaterializeExpiredAdvance()
	{
		return static::isMaterializeExpired(static::MATERIALIZE_ADVANCE);
	}

	public static function isMaterializable()
	{
		if (!static::MATERIALIZE_HOURS || \Katu\Config\Env::getPlatform() == "dev") {
			return true;
		}

		return in_array((int)(new \Katu\Tools\Calendar\Time)->format("h"), explode(",", static::MATERIALIZE_HOURS));
	}

	public static function getMaterializedTable()
	{
		$tableClass = static::getTableClass()->getName();

		return new $tableClass(static::getConnection(), static::getMaterializedTableName());
	}

	public static function getMaterializedTableName()
	{
		$name = implode(static::SEPARATOR, [
			"mv",
			preg_replace("/^view_/", "", static::getViewName()->getPlain()),
		]);

		return new \Katu\PDO\Name($name);
	}

	public static function copy($sourceTable, $destinationTable)
	{
		@set_time_limit(static::TIMEOUT);

		// Get a temporary table.
		$temporaryTableName = new \Katu\PDO\Name("_tmp_" . strtoupper(\Katu\Tools\Random\Generator::getIdString(static::TMP_LENGTH)));
		$tableClass = static::getTableClass()->getName();
		$temporaryTable = new $tableClass($destinationTable->getConnection(), $temporaryTableName);

		// Copy into temporary table view.
		$params = [
			"disableNull" => true,
			"autoIndices" => static::AUTO_INDICES,
			"compositeIndex" => static::COMPOSITE_INDEX,
			"customIndices" => array_values(array_filter(explode(",", static::CUSTOM_INDICES))),
		];
		$sourceTable->copy($temporaryTable, $params);

		// Drop the original table.
		try {
			$destinationTable->delete();
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);
		}

		// Rename the temporary table.
		$temporaryTable->rename($destinationTable->name);

		return true;
	}

	public static function cache(): bool
	{
		foreach (static::getConnection()->getProcesslist() as $process) {
			if (preg_match("/CREATE TABLE.+AS SELECT \* FROM.+" . static::getViewName() . "/", $process->info)) {
				return false;
			}
		}

		$class = static::getClass()->getName();
		$class::materializeSourceViews();
		$class::copy($class::getView(), $class::generateCachedTable());
		$class::updateLastCachedTime();

		\Katu\Cache\Runtime::clear();

		return true;
	}

	public static function cacheIfExpired()
	{
		if (static::isCacheExpiredAdvance()) {
			return static::cache();
		}
	}

	public static function materialize()
	{
		try {
			$class = static::getClass()->getName();

			$callback = function ($class) {
				$class::materializeSourceViews();
				$class::copy($class::getView(), $class::getMaterializedTable());
				$class::updateLastMaterializedTime();

				\Katu\Cache\Runtime::clear();

				return true;
			};

			(new \Katu\Tools\Locks\Lock(new TIdentifier("databases", static::getConnection()->getConfig()->database, "views", "materialize", $class), new Timeout(static::TIMEOUT), $callback))
				->setArgs($class)
				->run()
				;
		} catch (\Katu\Exceptions\LockException $e) {
			// Nevermind.
		}
	}

	public static function materializeIfExpired()
	{
		if (static::isMaterializeExpiredAdvance()) {
			try {
				return static::materialize();
			} catch (\Throwable $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);
			}
		}
	}

	public static function materializeSourceViews()
	{
		foreach (static::getView()->getSourceViewsInMaterializedViews() as $view) {
			foreach ($view->getModels() as $class) {
				$class->getName()::materializeIfExpired();
			}
		}

		return true;
	}

	public static function getLastCachedTemporaryFile(): \Katu\Files\File
	{
		return new \Katu\Files\Temporary("databases", static::getConnection()->getConfig()->getDatabase(), "views", "cached", static::TABLE);
	}

	public static function updateLastCachedTime()
	{
		return static::getLastCachedTemporaryFile()->set(microtime(true));
	}

	public static function getLastCachedDateTime()
	{
		$query = static::getCachedTablesQuery();
		$array = $query->getResult()->getItems();
		$regexp = static::getCachedTableNameRegexp();

		if (($array[0]["TABLE_NAME"] ?? null) && preg_match("/$regexp/", $array[0]["TABLE_NAME"], $match)) {
			return \Katu\Tools\Calendar\Time::createFromFormat(static::CACHE_DATETIME_FORMAT, $match["datetime"]);
		}

		return false;
	}

	public static function getLastMaterializedTemporaryFile(): \Katu\Files\File
	{
		return new \Katu\Files\Temporary("databases", static::getConnection()->getConfig()->getDatabase(), "views", "materialized", static::TABLE);
	}

	public static function updateLastMaterializedTime()
	{
		return static::getLastMaterializedTemporaryFile()->set(microtime(true));
	}

	public static function getLastMaterializedTime()
	{
		return (float)static::getLastMaterializedTemporaryFile()->get();
	}

	public static function getAllViewClasses(): array
	{
		$dir = new \Katu\Files\File(\App\App::getAppDir(), "Models");
		if ($dir->exists()) {
			$dir->includeAllPhpFiles();
		}

		return array_values(array_filter(array_map(function ($className) {
			if (is_subclass_of($className, "Katu\Models\View") && defined("$className::TABLE") && $className::TABLE) {
				return new TClass($className);
			}
		}, get_declared_classes())));
	}

	public static function cacheAndMaterializeAll(int $limit = null)
	{
		array_map(function (TClass $class) {
			try {
				$stopwatch = new \Katu\Tools\Profiler\Stopwatch;
				$class->getName()::cacheIfExpired();
				if ($class->getName()::isMaterializable()) {
					$class->getName()::materializeIfExpired();
				}
				$stopwatch->finish();
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->debug(\Katu\Files\Formats\JSON::encodeInline([
					(string)$class,
					(string)$stopwatch->getMilliDuration(),
				]));
			} catch (\Throwable $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);
			}
		}, array_slice(static::getAllViewClasses(), 0, $limit));
	}

	public static function deleteOldCachedTables()
	{
		$tableClass = static::getTableClass()->getName();

		foreach (static::getAllViewClasses() as $class) {
			$query = $class->getName()::getCachedTablesQuery();
			$array = $query->getResult()->getItems();
			foreach (array_slice($array, 1) as $tableArray) {
				$table = new $tableClass(
					$class->getName()::getConnection(),
					new \Katu\PDO\Name($tableArray["TABLE_NAME"]),
				);
				$table->delete();
			}
		}

		return true;
	}
}
