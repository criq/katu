<?php

namespace Katu\Classes\Factories;

use \Sexy\Sexy as SX;

abstract class TableFactory extends Factory
{
	abstract public static function getTable() : \Katu\PDO\Table;

	public static function get(int $primaryKey)
	{
		$table = static::getTable();

		$sql = SX::select()
			->from($table)
			->where(SX::eq($table->getColumn($table->getIdColumnName()), $primaryKey))
			;

		return $table->getConnection()->createQuery($sql)
			->setFactory(new static)
			->getResult()
			->getOne()
			;
	}

	public static function getBySql($sql)
	{
		return static::getTable()->getConnection()->createQuery($sql)
			->setFactory(new static)
			->getResult()
			;
	}
}
