<?php

namespace Katu\PDO;

class ProcessCollection extends \ArrayObject
{
	public function filterQueries(): ProcessCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Process $process) {
			return $process->getCommand() == "Query";
		})));
	}
}
