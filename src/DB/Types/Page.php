<?php

namespace Katu\DB\Types;

class Page {

	const DEFAULT_PAGE    = 1;
	const DEFAULT_PERPAGE = 50;

	public $perpage = self::DEFAULT_PERPAGE;
	public $page    = self::DEFAULT_PAGE;

	public function __construct($page = self::DEFAULT_PAGE, $perpage = self::DEFAULT_PERPAGE) {
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
