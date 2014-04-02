<?php

namespace Jabli\Types;

class Pagination {

	const PAGINATION_ALL_PAGES_LIMIT = 10;
	const PAGINATION_ENDS_OFFSET     = 0;
	const PAGINATION_CURRENT_OFFSET  = 3;

	public $total;
	public $perpage;
	public $page;

	public function __construct($total, $perpage, $page) {
		if ((int) $total < 0) {
			throw new Exception("Invalid total.");
		}
		if ((int) $perpage < 1) {
			throw new Exception("Invalid per page.");
		}
		if ((int) $page < 1) {
			throw new Exception("Invalid page.");
		}

		$this->total   = (int) $total;
		$this->perpage = (int) $perpage;
		$this->page    = (int) $page;
	}

	static function getPageIdent() {
		return \Jabli\Config::getApp('pagination', 'page_ident');
	}

	static function getPageFromRequest($params) {
		if (!isset($params[self::getPageIdent()])) {
			return 1;
		}

		if ($params[self::getPageIdent()] < 1) {
			return 1;
		}

		return (int) $params[self::getPageIdent()];
	}

	public function getMinPage() {
		return (int) 1;
	}

	public function getMaxPage() {
		return (int) (ceil($this->total / $this->perpage));
	}

	public function getPaginationPages($options = array()) {
		$options = array_merge(array(
			'allPagesLimit' => self::PAGINATION_ALL_PAGES_LIMIT,
			'endsOffset'    => self::PAGINATION_ENDS_OFFSET,
			'currentOffset' => self::PAGINATION_CURRENT_OFFSET,
		), $options);

		if ($this->getMaxPage() <= $options['allPagesLimit']) {
			return range($this->getMinPage(), $this->getMaxPage());
		}

		$pages = array();
		$pages = array_merge($pages, range($this->getMinPage(), $this->getMinPage() + $options['endsOffset']));
		$pages = array_merge($pages, range($this->page - $options['currentOffset'], $this->page + $options['currentOffset']));
		$pages = array_merge($pages, range($this->getMaxPage() - $options['endsOffset'], $this->getMaxPage()));

		$pages = array_unique(array_filter($pages, function($i){
			return ($i > 0 && $i <= $this->getMaxPage()) ? TRUE : FALSE;
		}));
		natsort($pages);
		$pages = array_values($pages);

		return $pages;
	}

}
