<?php

namespace Katu\Models;

class ColumnValueCollection extends \ArrayObject
{
	public function getColumnsString(): string
	{
		return implode(", ", array_map(function (ColumnValue $columnValue) {
			return (string)$columnValue->getColumn();
		}, $this->getArrayCopy()));
	}

	public function getParamsString(): string
	{
		return implode(", ", array_map(function (ColumnValue $columnValue) {
			return ":{$columnValue->getStatementKey()}";
		}, $this->getArrayCopy()));
	}

	public function getSetString(): string
	{
		return implode(", ", array_map(function (ColumnValue $columnValue) {
			return "{$columnValue->getColumn()} = :{$columnValue->getStatementKey()}";
		}, $this->getArrayCopy()));
	}

	public function getStatementParams(): array
	{
		$res = [];
		foreach ($this as $columnValue) {
			$res[$columnValue->getStatementKey()] = $columnValue->getStatementValue();
		}

		return $res;
	}
}
