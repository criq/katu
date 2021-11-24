<?php

namespace Katu\PDO;

class Processlist extends \ArrayObject
{
	public function filterQueries(): Processlist
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Process $process) {
			return $process->command == "Query";
		})));
	}
}
