<?php

namespace Katu\Doctrine;

class QueryBuilderResult {

	public $data;
	public $pagination;

	public function __construct($queryBuider, $page = NULL, $perPage = NULL) {
		$this->queryBuider = $queryBuider;

		if (!is_null($page) && !is_null($perPage)) {
			$queryBuider->setFirstResult(($page * $perPage) - $perPage);
			$queryBuider->setMaxResults($perPage);
		}

		$query = $queryBuider->getQuery();
		$this->data = $query->getResult();

		if (!is_null($page) && !is_null($perPage)) {
			$paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
			$total = count($paginator);
			$this->pagination = new \Katu\Types\TPagination($total, $perPage, $page);
		} else {
			$total = count($this->data);
			$this->pagination = new \Katu\Types\TPagination($total, $total, 1);
		}
	}

}
