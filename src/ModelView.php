<?php

namespace Katu;

class ModelView extends ReadOnlyModel {

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

			$sourceTables = static::getSourceTables();
			foreach ($sourceTables as $sourceTable) {

				$table = new \Katu\Pdo\Table(static::getPdo(), $sourceTable);
				if (!$table->exists()) {
					continue;
				}

				$lastUpdatedTime = $table->getLastUpdatedTime();
				if (!is_null($lastUpdatedTime) && $lastUpdatedTime > $lastCachedTime) {
					return true;
				}

			}

		}

		return false;
	}

	static function getSourceTables() {
		return (new \Katu\Pdo\Table(static::getPdo(), static::TABLE))->getSourceTables();
	}

	static function getCachedName() {
		return ['!databases', '!' . parent::getTable()->pdo->name, '!views', '!cached', '!' . static::TABLE];
	}

	static function resetCache() {
		return \Katu\Utils\Cache::reset(static::getCachedName());
	}

	static function getTableName() {
		if (static::isCached()) {
			return implode('_', [
				'_cache',
				parent::getTableName(),
			]);
		}

		return parent::getTableName();
	}

	static function getMaterializedTableName() {
		return implode('_', [
			'_materialized',
			preg_replace('#^view_#', null, parent::getTableName()),
		]);
	}

	static function getTable() {
		// Do we want to cache?
		if (static::isExpired()) {
			static::refreshCache();
		}

		return parent::getTable();
	}

	static function refreshCache() {
		$sourceTable      = new \Katu\Pdo\Table(new \Katu\Pdo\Connection(static::DATABASE), static::TABLE);
		$destinationTable = new \Katu\Pdo\Table(static::getPdo(), static::getTableName());

		return static::refresh($sourceTable, $destinationTable);
	}

	static function refreshMaterialized() {
		$sourceTable      = new \Katu\Pdo\Table(new \Katu\Pdo\Connection(static::DATABASE), static::TABLE);
		$destinationTable = new \Katu\Pdo\Table(static::getPdo(), static::getMaterializedTableName());

		return static::refresh($sourceTable, $destinationTable);
	}

	static function refresh($sourceTable, $destinationTable) {
		set_time_limit(600);

		// Get a temporary table.
		$temporaryTableName = '_tmp_' . \Katu\Utils\Random::getIdString(8);
		$temporaryTable = new \Katu\Pdo\Table($destinationTable->pdo, $temporaryTableName);

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

		// Save the last cached time.
		static::updateLastCachedTime();

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

}
