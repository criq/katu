<?php

namespace Katu;

class ViewModel extends ModelBase {

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

	static function isCached() {
		return static::$_cache;
	}

	static function isExpired() {
		if (!static::isCached()) {
			return false;
		}

		// Cached table doesn't exist.
		if (!in_array(static::getTableName(), static::getPdo()->getTableNames())) {
			return true;
		}

		$lastCachedTime = static::getLastCachedTime();

		// No cached time.
		if (!$lastCachedTime) {
			return true;
		}

		// Expired.
		if (!is_null($lastCachedTime) && $lastCachedTime < time() - static::$_cacheTimeout) {
			return true;
		}

		// Expired data in tables.
		if (static::$_cacheOnUpdate) {

			$sourceTables = static::getView()->getSourceTables();
			foreach ($sourceTables as $sourceTable) {

				if (!$sourceTable->exists()) {
					continue;
				}

				$lastUpdatedTime = $sourceTable->getLastUpdatedTime();
				if (!is_null($lastUpdatedTime) && $lastUpdatedTime > $lastCachedTime) {
					return true;
				}

			}

		}

		return false;
	}

	static function getTable() {
		if (static::isExpired()) {
			static::cache();
		}

		return static::isCached() ? static::getCachedTable() : static::getView();
	}

	static function getTableName() {
		return static::isCached() ? static::getCachedTableName() : static::getViewName();
	}

	static function getView() {
		return new Pdo\View(static::getPdo(), static::getViewName());
	}

	static function getViewName() {
		return static::TABLE;
	}

	static function getCachedTable() {
		return new Pdo\Table(static::getPdo(), static::getCachedTableName());
	}

	static function getCachedTableName() {
		$name = implode('_', [
			'_cache',
			static::getViewName(),
		]);

		if (strlen($name) > 64) {
			return substr($name, 0, 60) . substr(sha1($name), 0, 4);
		}

		return $name;
	}

	static function getCachedTableCacheName() {
		return ['!databases', '!' . static::getView()->pdo->name, '!views', '!cachedView', '!' . static::TABLE];
	}

	static function resetCache() {
		return \Katu\Utils\Cache::reset(static::getCachedTableCacheName());
	}

	static function getMaterializedTable() {
		return new Pdo\Table(static::getPdo(), static::getMaterializedTableName());
	}

	static function getMaterializedTableName() {
		return implode('_', [
			'mv',
			preg_replace('#^view_#', null, static::getViewName()),
		]);
	}

	static function copy($sourceTable, $destinationTable) {
		set_time_limit(600);

		// Get a temporary table.
		$temporaryTableName = '_tmp_' . \Katu\Utils\Random::getIdString(8);
		$temporaryTable = new Pdo\Table($destinationTable->pdo, $temporaryTableName);

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

		}

		// Rename the temporary table.
		$temporaryTable->rename($destinationTable->name);

		return true;
	}

	static function cache() {
		static::copy(static::getView(), static::getCachedTable());
		static::updateLastCachedTime();

		return true;
	}

	static function materialize() {
		static::copy(static::getView(), static::getMaterializedTable());
		static::updateLastMaterializedTime();

		return true;
	}

	static function getLastCachedTmpName() {
		return ['!databases', '!' . static::getPdo()->name, '!views', '!cached', '!' . static::TABLE];
	}

	static function updateLastCachedTime() {
		return \Katu\Utils\Tmp::set(static::getLastCachedTmpName(), microtime(true));
	}

	static function getLastCachedTime() {
		return \Katu\Utils\Tmp::get(static::getLastCachedTmpName());
	}

	static function getLastMaterializedTmpName() {
		return ['!databases', '!' . static::getPdo()->name, '!views', '!materialized', '!' . static::TABLE];
	}

	static function updateLastMaterializedTime() {
		return \Katu\Utils\Tmp::set(static::getLastMaterializedTmpName(), microtime(true));
	}

	static function getLastMaterializedTime() {
		return \Katu\Utils\Tmp::get(static::getLastMaterializedTmpName());
	}

	static function getAllViewModelNames() {
		(new \Katu\Utils\File('app/Models/Views'))->includeAllPhpFiles();

		return array_values(array_filter(array_filter(get_declared_classes(), function($i) {
			return strpos($i, 'App\\Models\\Views\\') === 0;
		})));
	}

	static function getAllViewModelInfo() {
		$res = [];

		foreach (static::getAllViewModelNames() as $viewModelName) {
			$class = '\\' . $viewModelName;

			$property = [
				'class' => $viewModelName,
				'cache' => [
					'on' => $class::$_cache,
					'timeout' => $class::$_cacheTimeout,
					'advance' => $class::$_cacheAdvance,
					'onUpdate' => $class::$_cacheOnUpdate,
					'time' => $class::getLastCachedTime(),
				],
				'materialize' => [
					'on' => $class::$_materialize,
					'timeout' => $class::$_materializeTimeout,
					'advance' => $class::$_materializeAdvance,
					'time' => $class::getLastMaterializedTime(),
				],
			];

			$property['cache']['age'] = time() - $property['cache']['time'];
			$property['cache']['ratio'] = $property['cache']['age'] / $property['cache']['timeout'];
			$property['cache']['expired'] = $property['cache']['ratio'] > 1;
			$property['cache']['expiredAdvance'] = $property['cache']['ratio'] > $property['cache']['advance'];

			$property['materialize']['age'] = time() - $property['materialize']['time'];
			$property['materialize']['ratio'] = $property['materialize']['age'] / $property['materialize']['timeout'];
			$property['materialize']['expired'] = $property['materialize']['ratio'] > 1;
			$property['materialize']['expiredAdvance'] = $property['materialize']['ratio'] > $property['materialize']['advance'];

			$res[] = $property;
		}

		return $res;
	}

	static function getAllCacheExpired() {
		return array_values(array_filter(static::getAllViewModelInfo(), function($i) {
			return $i['cache']['on'] && $i['cache']['expired'];
		}));
	}

	static function getAllCacheExpiredAdvance() {
		return array_values(array_filter(static::getAllViewModelInfo(), function($i) {
			return $i['cache']['on'] && $i['cache']['expiredAdvance'];
		}));
	}

	static function getAllMaterializeExpired() {
		return array_values(array_filter(static::getAllViewModelInfo(), function($i) {
			return $i['materialize']['on'] && $i['materialize']['expired'];
		}));
	}

	static function getAllMaterializeExpiredAdvance() {
		return array_values(array_filter(static::getAllViewModelInfo(), function($i) {
			return $i['materialize']['on'] && $i['materialize']['expiredAdvance'];
		}));
	}

}
