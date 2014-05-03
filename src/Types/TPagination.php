<?php

namespace Katu\Types;

class TPagination {

	const PAGINATION_ALL_PAGES_LIMIT = 10;
	const PAGINATION_ENDS_OFFSET     = 0;
	const PAGINATION_CURRENT_OFFSET  = 3;

	public $total;
	public $perPage;
	public $page;

	public function __construct($total, $perPage, $page) {
		if ((int) $total < 0) {
			throw new \Exception("Invalid total.");
		}
		if ((int) $perPage < 1) {
			throw new \Exception("Invalid per page.");
		}
		if ((int) $page < 1) {
			throw new \Exception("Invalid page.");
		}

		$this->total   = (int) $total;
		$this->perPage = (int) $perPage;
		$this->page    = (int) $page;
	}

	static function getPageIdent() {
		return \Katu\Config::get('app', 'pagination', 'pageIdent');
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
		return (int) (ceil($this->total / $this->perPage));
	}

	public function getPaginationPages($options = array()) {
		$options = array_merge(array(
			'allPagesLimit' => self::PAGINATION_ALL_PAGES_LIMIT,
			'endsOffset'    => self::PAGINATION_ENDS_OFFSET,
			'currentOffset' => self::PAGINATION_CURRENT_OFFSET,
		), $options);

		if (!$this->total) {
			return array();
		}

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
