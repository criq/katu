<?php

namespace Katu\Doctrine;

class QueryResult {

	public $data;
	public $pagination;

	public function __construct($query, $page = NULL, $perpage = NULL) {
		$this->data = $query->getResult();
		$paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

		$total = count($paginator);

		if (!is_null($page) && !is_null($perpage)) {
			$this->pagination = new \Katu\Types\TPagination($total, $perpage, $page);
		} else {
			$this->pagination = new \Katu\Types\TPagination($total, $total, 1);
		}
	}

}
