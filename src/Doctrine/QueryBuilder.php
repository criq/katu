<?php

namespace Katu\Doctrine;

class QueryBuilder {

	private $queryBuilder;
	private $page    = NULL;
	private $perpage = NULL;

	public function __construct($queryBuilder, $alias) {
		$this->queryBuilder = $queryBuilder;
		$this->queryBuilder->select($alias);

		return $this;
	}

	public function __call($callable, $args) {
		call_user_func_array(array($this->queryBuilder, $callable), $args);

		return $this;
	}

	public function setPaging($page, $perpage) {
		$this->queryBuilder->setFirstResult(($page * $perpage) - $perpage)->setMaxResults($perpage);
		$this->page = $page;
		$this->perpage = $perpage;

		return $this;
	}

	public function getResult() {
		return new QueryResult($this->queryBuilder->getQuery(), $this->page, $this->perpage);
	}

}
