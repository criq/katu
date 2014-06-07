<?php

namespace Katu\PDO\Meta;

class Select {

	public $select = array();

	public function __construct($select) {
		$this->select = (array) $select;
	}

	public function getSelect() {
		return implode(', ', $this->select);
	}

}
