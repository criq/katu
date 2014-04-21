<?php

namespace Katu\Doctrine;

class QueryBuilder {

	private $queryBuilder;
	private $page    = NULL;
	private $perPage = NULL;

	public function __construct($queryBuilder, $class = NULL, $alias = NULL) {
		$this->queryBuilder = $queryBuilder;

		if (!is_null($class)) {
			$this->queryBuilder->select($alias);
		}

		if (!is_null($class) && !is_null($alias)) {
			$this->queryBuilder->from($class, $alias);
		}

		return $this;
	}

	public function __call($callable, $args) {
		call_user_func_array(array($this->queryBuilder, $callable), $args);

		return $this;
	}

	public function setPaging($page, $perPage) {
		$this->queryBuilder->setFirstResult(($page * $perPage) - $perPage)->setMaxResults($perPage);
		$this->page = $page;
		$this->perPage = $perPage;

		return $this;
	}

	public function getResult() {
		return new QueryResult($this->queryBuilder->getQuery(), $this->page, $this->perPage);
	}

}
