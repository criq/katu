<?php

namespace Katu\Tools\Jobs;

class JobCollection extends \ArrayObject
{
	public function filterExpired(): JobCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Job $job) {
			return $job->isExpired();
		})));
	}
}
