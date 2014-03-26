<?php

namespace Jabli\DB\Types;

class Page {

	public $page;
	public $perpage;

	public function __construct($page, $perpage) {
		$this->page    = $page;
		$this->perpage = $perpage;
	}

	public function getLimit() {
		return (($this->page * $this->perpage) - $this->perpage) . ', ' . $this->perpage;
	}

}
