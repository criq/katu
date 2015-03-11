<?php

namespace Katu\Utils\Profiler;

class Query {

	public function __construct($query, $duration) {
		$this->query    = (string) (trim($query));
		$this->duration = (float)  ($duration);
	}

}
