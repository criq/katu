<?php

namespace Katu;

class ModelView extends ReadOnlyModel {

	const CACHE = 86400;

	static function isCached() {
		return defined('static::CACHE') && static::CACHE;
	}

	static function isExpired() {
		if (!static::isCached()) {
			return false;
		}

		$lastCachedTime = static::getLastCachedTime();

		// No cached time.
		if (!$lastCachedTime) {
			return true;
		}

		// Expired.
		if (!is_null($lastCachedTime) && $lastCachedTime < time() - static::CACHE) {
			return true;
		}

		foreach (static::getSourceTables() as $sourceTable) {
			$table = new \Katu\Pdo\Table(static::getPdo(), $sourceTable);
			$lastUpdatedTime = $table->getLastUpdatedTime();

			if (!is_null($lastUpdatedTime) && $lastUpdatedTime > $lastCachedTime) {
				return true;
			}
		}

		return false;
	}

	static function getSourceTables() {
		$pdo = static::getPdo();
		$table = "`" . static::DATABASE . "`.`" . static::TABLE . "`";

		return \Katu\Utils\Cache::get(static::getSourceTablesName(), function() use($pdo, $table) {
			$tables = [];

			$sql = " EXPLAIN SELECT * FROM " . $table . " ";
			$res = $pdo->createQuery($sql)->getResult()->getArray();
			foreach ($res as $row) {
				if (!preg_match('#^<derived[0-9]+>$#', $row['table'])) {
					$tables[] = $row['table'];
				}
			}

			return array_values(array_unique($tables));
		});
	}

	static function getSourceTablesName() {
		return ['!databases', '!' . parent::getTable()->pdo->name, '!views', '!sourceTables', '!' . static::TABLE];
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

	static function getTable() {
		// Do we want to materialize?
		if (static::isExpired()) {
			$sourceTable      = new \Katu\Pdo\Table(new \Katu\Pdo\Connection(static::DATABASE), static::TABLE);
			$destinationTable = new \Katu\Pdo\Table(static::getPdo(), static::getTableName());

			static::refresh($sourceTable, $destinationTable);
			static::updateLastCachedTime();
		}

		return parent::getTable();
	}

	static function refresh($sourceTable, $destinationTable) {
		// Copy into materialized view.
		return $sourceTable->copy($destinationTable, [
			'disableNull'   => true,
			'createIndices' => true,
		]);
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
