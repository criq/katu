<?php

namespace Katu\Pdo\Expressions;

class Page {

	const DEFAULT_PAGE    = 1;
	const DEFAULT_PERPAGE = 50;

	public $perPage = self::DEFAULT_PERPAGE;
	public $page    = self::DEFAULT_PAGE;

	public function __construct($page = self::DEFAULT_PAGE, $perPage = self::DEFAULT_PERPAGE) {
		if (!(int) $perPage) {
			throw new \Exception("Invalid per page.");
		}
		if (!(int) $page) {
			throw new \Exception("Invalid page.");
		}

		$this->perPage = (int) $perPage;
		$this->page    = (int) $page;
	}

	public function getOffset() {
		return (int) (($this->page * $this->perPage) - $this->perPage);
	}

	public function getLimit() {
		return (int) $this->perPage;
	}

	public function getSql(&$context = array()) {
		$context['bindValues']['pageOffset'] = $this->getOffset();
		$context['bindValues']['pageLimit']  = $this->getLimit();

		return " :pageOffset, :pageLimit ";
	}

}
