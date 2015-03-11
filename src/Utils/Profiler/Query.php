<?php

namespace Katu\Utils\Profiler;

class Query {

	public function __construct($query, $duration) {
		if ($query instanceof \Katu\Pdo\Query) {
			$this->query = $query->getStatement()->queryString;
		} else {
			$this->query = (string) (trim($query));
		}

		if ($duration instanceof \Katu\Utils\Stopwatch) {
			$this->duration = (int) ($duration->getMicroDuration());
		} else {
			$this->duration = (int) ($duration);
		}
	}

}
