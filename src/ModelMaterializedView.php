<?php

namespace Katu;

class ModelMaterializedView extends Model {

	const TIMEOUT = 86400;

	static function getTable() {
		$sourceTable      = new \Katu\Pdo\Table(new \Katu\Pdo\Connection(static::SOURCE_DATABASE), static::SOURCE_TABLE);
		$destinationTable = new \Katu\Pdo\Table(static::getPdo(), static::TABLE);

		\Katu\Utils\Cache::get(['!materializedViews', '!refreshed', static::getTableName()], function() use($sourceTable, $destinationTable) {
			return static::refresh($sourceTable, $destinationTable);
		}, static::TIMEOUT);

		return parent::getTable();
	}

	static function refresh($sourceTable, $destinationTable) {
		// Copy into materialized view.
		return $sourceTable->copy($destinationTable, [
			'disableNull' => true,
			'createIndices' => true,
		]);
	}

}
