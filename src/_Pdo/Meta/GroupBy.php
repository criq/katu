<?php

namespace Katu\PDO\Meta;

class GroupBy {

	public $groupBy;

	public function __construct($groupBy) {
		$this->groupBy = (string) $groupBy;
	}

	public function getGroupBy() {
		return $this->groupBy;
	}

}
