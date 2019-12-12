<?php

namespace Katu\Models;

abstract class View extends Base {

	const TABLE = null;
	const SEPARATOR = '_';
	const TMP_LENGTH = 8;
	const PREFIX_CACHE = '_cache';
	const CACHE_DATETIME_FORMAT = 'YmdHis';

	static $_cache              = true;
	static $_cacheTimeout       = 86400;
	static $_cacheOnUpdate      = true;
	static $_cacheAdvance       = .75;
	static $_materialize        = false;
	static $_materializeTimeout = 86400;
	static $_materializeAdvance = 1;
	static $_materializeHours   = [];
	static $_autoIndices        = true;
	static $_compositeIndex     = true;
	static $_customIndices      = [];

	static function getTable() {
		static::cacheIfExpired();

		return static::isCached() ? static::getCachedTable() : static::getView();
	}

	static function getTableName() {
		return static::isCached() ? static::getCachedTableName() : static::getViewName();
	}

	static function getView() {
		return new \Katu\PDO\View(static::getConnection(), static::getViewName());
	}

	static function getViewName() {
		return new \Katu\PDO\Name(static::TABLE);
	}

	static function getColumn($name, $options = []) {
		if (isset($options['cache']) && $options['cache'] === false) {
			$table = static::getView();
		} else {
			$table = static::getTable();
		}

		return new \Katu\PDO\Column($table, new \Katu\PDO\Name($name));
	}

	static function getViewColumn($name, $options = []) {
		$options['cache'] = false;

		return static::getColumn($name, $options);
	}

	static function getCachedTableNameBase() {
		return implode(static::SEPARATOR, [
			static::PREFIX_CACHE,
			static::getViewName()->getName(),
		]);
	}

	static function getCachedTable() {
		try {

			// Try cached table name.
			$tableName = static::getCachedTableName();

		} catch (\Throwable $e) {

			// Some error happened, probably locked, return normal view.
			$tableName = static::getViewName();

		}

		return new \Katu\PDO\Table(static::getConnection(), $tableName);
	}

	static function getCachedTablesSql() {
		$sql = " SELECT *
			FROM information_schema.tables
			WHERE TABLE_SCHEMA = :tableSchema
			AND TABLE_NAME REGEXP :tableRegexp
			ORDER BY TABLE_NAME DESC ";

		return $sql;
	}

	static function getCachedTablesQuery() {
		$sql = static::getCachedTablesSql();

		$query = static::getConnection()->createQuery($sql, [
			'tableSchema' => static::getConnection()->config->database,
			'tableRegexp' => implode(static::SEPARATOR, [
				static::getCachedTableNameBase(),
				'[0-9]{' . strlen((new \Katu\Tools\DateTime\DateTime)->format(static::CACHE_DATETIME_FORMAT)) . '}',
				'([0-9A-Z]{' . static::TMP_LENGTH . '})',
			]),
		]);

		return $query;
	}

	static function getCachedTableName() {
		$query = static::getCachedTablesQuery();
		$array = $query->getResult()->getArray();

		if ($array[0]['TABLE_NAME'] ?? null) {
			return new \Katu\PDO\Name($array[0]['TABLE_NAME']);
		}

		// No cached table found, cache!
		static::cache();

		// Try again after caching.
		return static::getCachedTableName();






		// TODO - ošéfovat
		$name = static::getCachedTableNameBase();
		if (strlen($name) > 64) {
			return substr($name, 0, 60) . substr(sha1($name), 0, 4);
		}

		return new \Katu\PDO\Name($name);
	}

	static function generateCachedTable() {
		return new \Katu\PDO\Table(static::getConnection(), static::generateCachedTableName());
	}

	static function generateCachedTableName() {
		$name = implode(static::SEPARATOR, array_merge([static::getCachedTableNameBase()], [
			(new \Katu\Tools\DateTime\DateTime)->format(static::CACHE_DATETIME_FORMAT),
			\Katu\Tools\Random\Generator::getIdString(static::TMP_LENGTH),
		]));

		if (strlen($name) > 64) {
			return substr($name, 0, 60) . substr(sha1($name), 0, 4);
		}

		return new \Katu\PDO\Name($name);
	}

	static function isCached() {
		return static::$_cache;
	}

	static function isMaterialized() {
		return static::$_materialize;
	}

	static function cachedTableExists() {
		return in_array(static::getCachedTableName(), static::getConnection()->getTableNames());
	}

	static function materializedTableExists() {
		return in_array(static::getMaterializedTableName(), static::getConnection()->getTableNames());
	}

	static function cacheHasUpdatedTables() {
		if (static::$_cacheOnUpdate) {

			$sourceTables = static::getView()->getSourceTables();
			foreach ($sourceTables as $sourceTable) {

				if (!$sourceTable->exists()) {
					continue;
				}

				$lastUpdatedTime = $sourceTable->getLastUpdatedTime();
				$lastCachedDateTime = static::getLastCachedDateTime();
				if ($lastUpdatedTime && $lastCachedDateTime && $lastUpdatedTime > $lastCachedDateTime->getTimestamp()) {
					return true;
				}

			}

		}

		return false;
	}

	static function getCacheAge() {
		$lastCachedDateTime = static::getLastCachedDateTime();

		return time() - ($lastCachedDateTime ? $lastCachedDateTime->getTimestamp() : 0);
	}

	static function getMaterializeAge() {
		return time() - static::getLastMaterializedTime();
	}

	static function getCacheExpiryRatio() {
		return static::getCacheAge() / static::$_cacheTimeout;
	}

	static function getMaterializeExpiryRatio() {
		return static::getMaterializeAge() / static::$_materializeTimeout;
	}

	static function isCacheExpired($expiryRatio = 1) {
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

	static function isCacheExpiredAdvance() {
		return static::isCacheExpired(static::$_cacheAdvance);
	}

	static function isMaterializeExpired($expiryRatio = 1) {
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

	static function isMaterializeExpiredAdvance($expiryRatio = 1) {
		return static::isMaterializeExpired(static::$_materializeAdvance);
	}

	static function isMaterializable() {
		if (!static::$_materializeHours || \Katu\Config\Env::getPlatform() == 'dev') {
			return true;
		}

		return in_array((int)(new \Katu\Tools\DateTime\DateTime)->format('h'), static::$_materializeHours);
	}

	static function getMaterializedTable() {
		return new \Katu\PDO\Table(static::getConnection(), static::getMaterializedTableName());
	}

	static function getMaterializedTableName() {
		$name = implode(static::SEPARATOR, [
			'mv',
			preg_replace('#^view_#', null, static::getViewName()->getName()),
		]);

		return new \Katu\PDO\Name($name);
	}

	static function copy($sourceTable, $destinationTable) {
		@set_time_limit(600);

		// Get a temporary table.
		$temporaryTableName = new \Katu\PDO\Name('_tmp_' . strtoupper(\Katu\Tools\Random\Generator::getIdString(static::TMP_LENGTH)));
		$temporaryTable = new \Katu\PDO\Table($destinationTable->getConnection(), $temporaryTableName);

		// Copy into temporary table view.
		$params = [
			'disableNull'    => true,
			'autoIndices'    => static::$_autoIndices,
			'compositeIndex' => static::$_compositeIndex,
			'customIndices'  => static::$_customIndices,
		];
		$sourceTable->copy($temporaryTable, $params);

		// Drop the original table.
		try {
			$destinationTable->delete();
		} catch (\Exception $e) {
			// Nevermind.
		}

		// Rename the temporary table.
		$temporaryTable->rename($destinationTable->name);

		return true;
	}

	static function cache() {
		$class = static::getClass();

		(new \Katu\Tools\Locks\Lock(3600, ['databases', static::getConnection()->config->database, 'views', 'cache', $class], function($class) {

			$class::materializeSourceViews();
			$class::copy($class::getView(), $class::generateCachedTable());
			$class::updateLastCachedTime();

		}))
			->setUseLock(false)
			->setArgs([$class])
			->run()
			;

		return true;
	}

	static function cacheIfExpired() {
		if (static::isCacheExpiredAdvance()) {
			try {
				return static::cache();
			} catch (\Throwable $e) {
				\App\Extensions\Errors\Handler::log($e);
			}
		}
	}

	static function materialize() {
		try {

			$class = static::getClass();

			(new \Katu\Tools\Locks\Lock(3600, ['databases', static::getConnection()->config->database, 'views', 'materialize', $class], function($class) {

				$class::materializeSourceViews();
				$class::copy($class::getView(), $class::getMaterializedTable());
				$class::updateLastMaterializedTime();

				return true;

			}))
				->setArgs([$class])
				->run()
				;

		} catch (\Katu\Exceptions\LockException $e) {
			// Nevermind.
		}
	}

	static function materializeIfExpired() {
		if (static::isMaterializeExpiredAdvance()) {
			try {
				return static::materialize();
			} catch (\Exception $e) {
				\App\Extensions\Errors\Handler::log($e);
			}
		}
	}

	static function materializeSourceViews() {
		foreach (static::getView()->getSourceViewsInMaterializedViews() as $view) {
			foreach ($view->getModelNames() as $class) {
				$class::materializeIfExpired();
			}
		}

		return true;
	}

	static function getLastCachedTemporaryFile() {
		return new \Katu\Files\Temporary(['!databases', '!' . static::getConnection()->config->database, '!views', '!cached', '!' . static::TABLE]);
	}

	static function updateLastCachedTime() {
		return static::getLastCachedTemporaryFile()->set(microtime(true));
	}

	static function getLastCachedDateTime() {
		$query = static::getCachedTablesQuery();
		$array = $query->getResult()->getArray();

		// Has CREATE_TIME.
		if (($array[0]['CREATE_TIME'] ?? null)) {
			return new \Katu\Tools\DateTime\DateTime($array[0]['CREATE_TIME']);

		// Load from table name.
		} elseif (($array[0]['TABLE_NAME'] ?? null) && preg_match('/^' . static::getCachedTableNameBase() . static::SEPARATOR . '(?<datetime>[0-9]{' . strlen((new \Katu\Tools\DateTime\DateTime)->format(static::CACHE_DATETIME_FORMAT)) . '})' . '/', $array[0]['TABLE_NAME'], $match)) {
			return \Katu\Tools\DateTime\DateTime::createFromFormat(static::CACHE_DATETIME_FORMAT, $match['datetime']);
		}

		return false;
	}

	static function getLastMaterializedTemporaryFile() {
		return new \Katu\Files\Temporary(['!databases', '!' . static::getConnection()->config->database, '!views', '!materialized', '!' . static::TABLE]);
	}

	static function updateLastMaterializedTime() {
		return static::getLastMaterializedTemporaryFile()->set(microtime(true));
	}

	static function getLastMaterializedTime() {
		return (float)static::getLastMaterializedTemporaryFile()->get();
	}

	static function getAllViewClassNames() {
		$dir = (new \Katu\Files\File('app', 'Models'));
		if ($dir->exists()) {
			$dir->includeAllPhpFiles();
		}

		return array_map(function($i) {
			return '\\' . ltrim($i, '\\');
		}, array_values(array_filter(get_declared_classes(), function($class) {
			return is_subclass_of($class, '\\Katu\\Models\\View') && defined("$class::TABLE") && $class::TABLE;
		})));
	}

	static function cacheAndMaterializeAll() {
		foreach (static::getAllViewClassNames() as $class) {
			$class::cacheIfExpired();
			if ($class::isMaterializable()) {
				$class::materializeIfExpired();
			}
		}
	}

}
