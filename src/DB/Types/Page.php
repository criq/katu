<?php

namespace Jabli\DB\Types;

class Page {

	public $perpage = 1;
	public $page    = 1;

	public function __construct($perpage = 1, $page = 1) {
		if (!(int) $perpage) {
			throw new Exception("Invalid per page.");
		}
		if (!(int) $page) {
			throw new Exception("Invalid page.");
		}

		$this->perpage = (int) $perpage;
		$this->page    = (int) $page;
	}

	public function getLimit() {
		return (($this->page * $this->perpage) - $this->perpage) . ', ' . $this->perpage;
	}

}
