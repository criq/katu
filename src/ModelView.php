<?php

namespace Katu;

class ModelView extends ReadOnlyModel {

	static function isCached() {
		return defined('static::CACHE') && static::CACHE;
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
		if (static::isCached()) {

 			$sourceTable      = new \Katu\Pdo\Table(new \Katu\Pdo\Connection(static::DATABASE), static::TABLE);
			$destinationTable = new \Katu\Pdo\Table(static::getPdo(), static::getTableName());

			\Katu\Utils\Cache::get(static::getCachedName(), function() use($sourceTable, $destinationTable) {
				return static::refresh($sourceTable, $destinationTable);
			}, static::CACHE);

			return parent::getTable();

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

}
