<?php

namespace Katu;

class ViewModel extends ModelBase {

	const CACHE                   = true;
	const CACHE_TIMEOUT           = 86400;
	const CACHE_REFRESH_ON_UPDATE = true;

	static $autoIndices   = true;
	static $customIndices = [];

	static function isCached() {
		return defined('static::CACHE') && static::CACHE;
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
		if (!is_null($lastCachedTime) && $lastCachedTime < time() - static::CACHE_TIMEOUT) {
			return true;
		}

		// Expired data in tables.
		if (static::CACHE_REFRESH_ON_UPDATE) {

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
			static::refreshCache();
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
		return implode('_', [
			'_cache',
			static::getViewName(),
		]);
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
			'_materialized',
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
			'disableNull'   => true,
			'autoIndices'   => static::$autoIndices,
			'customIndices' => static::$customIndices,
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

	static function refreshCache() {
		static::copy(static::getView(), static::getCachedTable());
		static::updateLastCachedTime();

		return true;
	}

	static function refreshMaterialized() {
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
		return ['!databases', '!' . static::getPdo()->name, '!views', '!cached', '!' . static::TABLE];
	}

	static function updateLastMaterializedTime() {
		return \Katu\Utils\Tmp::set(static::getLastMaterializedTmpName(), microtime(true));
	}

	static function getLastMaterializedTime() {
		return \Katu\Utils\Tmp::get(static::getLastMaterializedTmpName());
	}

}
