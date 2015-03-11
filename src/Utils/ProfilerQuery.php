<?php

namespace Katu\Utils;

class ProfilerQuery {

	public function __construct($query, $duration) {
		$this->query = $query;
		$this->duration = $duration;
	}

}
