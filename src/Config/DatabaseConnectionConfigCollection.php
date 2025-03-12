<?php

namespace Katu\Config;

class DatabaseConnectionConfigCollection extends \ArrayObject
{
	public function filterByTitle(string $title): DatabaseConnectionConfigCollection
	{
		return new static(array_filter($this->getArrayCopy(), function (DatabaseConnectionConfig $databaseConnectionConfig) use ($title) {
			return $databaseConnectionConfig->getTitle() == $title;
		}));
	}

	public function getFirst(): ?DatabaseConnectionConfig
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}
}
