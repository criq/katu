<?php

namespace Katu;

class ViewModel extends ModelBase
{
	const TABLE = null;

	public static $_autoIndices        = true;
	public static $_cache              = true;
	public static $_cacheAdvance       = .75;
	public static $_cacheOnUpdate      = true;
	public static $_cacheTimeout       = 86400;
	public static $_compositeIndex     = true;
	public static $_customIndices      = [];
	public static $_materialize        = false;
	public static $_materializeAdvance = 1;
	public static $_materializeHours   = [];
	public static $_materializeTimeout = 86400;

	public static function getTable()
	{
		static::cacheIfExpired();

		return static::isCached() ? static::getCachedTable() : static::getView();
	}

	public static function getTableName()
	{
		return static::isCached() ? static::getCachedTableName() : static::getViewName();
	}

	public static function getView()
	{
		return new Pdo\View(static::getPdo(), static::getViewName());
	}

	public static function getViewName()
	{
		return static::TABLE;
	}

	public static function getColumn($name, $options = [])
	{
		if (isset($options['cache']) && $options['cache'] === false) {
			$table = static::getView();
		} else {
			$table = static::getTable();
		}

		return new Pdo\Column($table, $name);
	}

	public static function getViewColumn($name, $options = [])
	{
		$options['cache'] = false;

		return static::getColumn($name, $options);
	}

	public static function getCachedTable()
	{
		return new Pdo\Table(static::getPdo(), static::getCachedTableName());
	}

	public static function getCachedTableName()
	{
		$name = implode('_', [
			'_cache',
			static::getViewName(),
		]);

		if (strlen($name) > 64) {
			return substr($name, 0, 60) . substr(sha1($name), 0, 4);
		}

		return $name;
	}

	public static function getCachedTableCacheName()
	{
		return ['!databases', '!' . static::getView()->pdo->name, '!views', '!cachedView', '!' . static::TABLE];
	}

	public static function isCached()
	{
		return static::$_cache;
	}

	public static function isMaterialized()
	{
		return static::$_materialize;
	}

	public static function cachedTableExists()
	{
		return in_array(static::getCachedTableName(), static::getPdo()->getTableNames());
	}

	public static function materializedTableExists()
	{
		return in_array(static::getMaterializedTableName(), static::getPdo()->getTableNames());
	}

	public static function cacheHasUpdatedTables()
	{
		if (static::$_cacheOnUpdate) {
			$sourceTables = static::getView()->getSourceTables();
			foreach ($sourceTables as $sourceTable) {
				if (!$sourceTable->exists()) {
					continue;
				}
				$lastUpdatedTime = $sourceTable->getLastUpdatedTime();
				if (!is_null($lastUpdatedTime) && $lastUpdatedTime > static::getLastCachedTime()) {
					return true;
				}
			}
		}

		return false;
	}

	public static function getCacheAge()
	{
		return time() - static::getLastCachedTime();
	}

	public static function getMaterializeAge()
	{
		return time() - static::getLastMaterializedTime();
	}

	public static function getCacheExpiryRatio()
	{
		return static::getCacheAge() / static::$_cacheTimeout;
	}

	public static function getMaterializeExpiryRatio()
	{
		return static::getMaterializeAge() / static::$_materializeTimeout;
	}

	public static function isCacheExpired($expiryRatio = 1)
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

	public static function isCacheExpiredAdvance()
	{
		return static::isCacheExpired(static::$_cacheAdvance);
	}

	public static function isMaterializeExpired($expiryRatio = 1)
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

	public static function isMaterializeExpiredAdvance($expiryRatio = 1)
	{
		return static::isMaterializeExpired(static::$_materializeAdvance);
	}

	public static function isMaterializable()
	{
		if (!static::$_materializeHours || \Katu\Env::getPlatform() == 'dev') {
			return true;
		}

		return in_array((int) (new \Katu\Utils\DateTime)->format('h'), static::$_materializeHours);
	}

	public static function resetCache()
	{
		return \Katu\Utils\Cache::reset(static::getCachedTableCacheName());
	}

	public static function getMaterializedTable()
	{
		return new Pdo\Table(static::getPdo(), static::getMaterializedTableName());
	}

	public static function getMaterializedTableName()
	{
		return implode('_', [
			'mv',
			preg_replace('#^view_#', null, static::getViewName()),
		]);
	}

	public static function copy($sourceTable, $destinationTable)
	{
		@set_time_limit(600);

		// Get a temporary table.
		$temporaryTableName = '_tmp_' . strtoupper(\Katu\Utils\Random::getIdString(8));
		$temporaryTable = new Pdo\Table($destinationTable->pdo, $temporaryTableName);

		// Copy into temporary table view.
		$params = [
			'disableNull'    => true,
			'autoIndices'    => static::$_autoIndices,
			'compositeIndex' => static::$_compositeIndex,
			'customIndices'  => static::$_customIndices,
		];

		// Copy tables.
		$sourceTable->copy($temporaryTable, $params);

		// Drop the original table.
		try {
			if ($destinationTable->exists()) {
				$destinationTable->delete();
			}
			// Rename the temporary table.
			$temporaryTable->rename($destinationTable->name);
		} catch (\Throwable $e) {
			// There was an error, delete the temporary table.
			$temporaryTable->delete();
		}

		return true;
	}

	public static function cache()
	{
		try {
			$class = static::getClass();
			$class = '\\' . ltrim($class, '\\');
			// var_dump($class);die;

			$class::materializeSourceViews();
			// var_dump($class::getView(), $class::getCachedTable());

			$class::copy($class::getView(), $class::getCachedTable());
			$class::updateLastCachedTime();

			return true;
		} catch (\Katu\Exceptions\LockException $e) {
			// Nevermind.
		}
	}

	public static function cacheIfExpired()
	{
		if (static::isCacheExpiredAdvance()) {
			try {
				return static::cache();
			} catch (\Exception $e) {
				\Katu\ErrorHandler::log($e);
			}
		}
	}

	public static function materialize()
	{
		try {
			return \Katu\Utils\Lock::run(['databases', static::getPdo()->config->database, 'views', 'materialize', static::TABLE], 600, function ($class) {
				$class::materializeSourceViews();
				$class = '\\' . ltrim($class, '\\');
				$class::copy($class::getView(), $class::getMaterializedTable());
				$class::updateLastMaterializedTime();

				return true;
			}, static::getClass());
		} catch (\Katu\Exceptions\LockException $e) {
			\Katu\ErrorHandler::log($e);
		}
	}

	public static function materializeIfExpired()
	{
		if (static::isMaterializeExpiredAdvance()) {
			try {
				return static::materialize();
			} catch (\Exception $e) {
				\Katu\ErrorHandler::log($e);
			}
		}
	}

	public static function materializeSourceViews()
	{
		foreach (static::getView()->getSourceViewsInMaterializedViews() as $view) {
			foreach ($view->getModelNames() as $class) {
				$class = '\\' . ltrim($class, '\\');
				$class::materializeIfExpired();
			}
		}

		return true;
	}

	public static function getLastCachedTmpName()
	{
		return ['!databases', '!' . static::getPdo()->config->database, '!views', '!cached', '!' . static::TABLE];
	}

	public static function updateLastCachedTime()
	{
		return \Katu\Utils\Tmp::set(static::getLastCachedTmpName(), microtime(true));
	}

	public static function getLastCachedTime()
	{
		return (float) \Katu\Utils\Tmp::get(static::getLastCachedTmpName());
	}

	public static function getLastMaterializedTmpName()
	{
		return ['!databases', '!' . static::getPdo()->config->database, '!views', '!materialized', '!' . static::TABLE];
	}

	public static function updateLastMaterializedTime()
	{
		return (float) \Katu\Utils\Tmp::set(static::getLastMaterializedTmpName(), microtime(true));
	}

	public static function getLastMaterializedTime()
	{
		return \Katu\Utils\Tmp::get(static::getLastMaterializedTmpName());
	}

	public static function getAllViewModelNames($directories = [])
	{
		try {
			$dir = (new \Katu\Utils\File('app', 'Models', 'Views'));
			if ($dir->exists()) {
				$dir->includeAllPhpFiles();
			}
		} catch (\Exception $e) {
			// Nevermind.
		}

		return array_values(array_filter(array_filter(get_declared_classes(), function ($i) {
			return strpos($i, 'App\\Models\\Views\\') === 0;
		})));
	}

	public static function cacheAndMaterializeAll()
	{
		foreach (static::getAllViewModelNames() as $modelView) {
			$class = '\\' . $modelView;

			$class::cacheIfExpired();

			if ($class::isMaterializable()) {
				$class::materializeIfExpired();
			}
		}
	}
}
