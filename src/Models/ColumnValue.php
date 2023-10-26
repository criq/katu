<?php

namespace Katu\Models;

use Katu\PDO\Column;

class ColumnValue
{
	public $column;
	public $value;

	public function __construct(Column $column, $value)
	{
		$this->column = $column;
		$this->value = $value;
	}

	public function getColumn(): Column
	{
		return $this->column;
	}

	public function getStatementKey(): string
	{
		return $this->getColumn()->getName()->getPlain();
	}

	public function getStatementValue(): ?string
	{
		return is_null($this->value) ? null : (string)$this->value;
	}
}
