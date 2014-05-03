<?php

namespace Katu\PDO;

use \PDO;

class Result {

	public $pdo;
	public $statement;
	public $params = array();
	public $meta   = array();
	public $class;

	public function __construct($pdo, $statement, $params = array(), $meta = array()) {
		$this->pdo       = $pdo;
		$this->statement = $statement;
		$this->params    = $params;
		$this->meta      = $meta;

		$this->statement->execute();

		$page = $this->getPageFromMeta();

		if ($page && strpos($this->statement->queryString, 'SQL_CALC_FOUND_ROWS')) {
			$total = $this->pdo->createQuery("SELECT FOUND_ROWS() AS total")->getResult()->statement->fetchColumn();
		} else {
			$total = count(iterator_to_array($this->statement));
		}

		if ($page) {
			$this->pagination = new \Katu\Types\TPagination($total, $page->perPage, $page->page);
		} else {
			$this->pagination = new \Katu\Types\TPagination($total, $total, 1);
		}
	}

	public function getPageFromMeta() {
		foreach ($this->meta as $_meta) {
			if ($_meta instanceof \Katu\PDO\Meta\Page) {
				return $_meta;
			}
		}

		return FALSE;
	}



	// Pagination.
	public function getPagination() {
		return new \Katu\Types\TPagination($this->statement->found_rows, $this->statement->limit, $this->statement->page);
	}

	public function getTotal() {
		return $this->pagination->total;
	}

	public function getPage() {
		return $this->pagination->page;
	}

	public function getPerPage() {
		return $this->pagination->perPage;
	}

	public function getCount() {
		return count(iterator_to_array($this->statement));
	}



	// Arrays.
	public function getAssoc() {
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}



	// Class.
	public function setClass($class) {
		$this->class = $class;
	}



	// Objects.
	public function getOne($class = NULL) {
		if (!$class && $this->class) {
			$class = $this->class;
		}

		$object = $class::getFromAssoc($this->statement->fetchObject($class));
		if ($object) {
			$object->save();
		}

		return $object;
	}

	public function getObjects($class = NULL) {
		if (!$class && $this->class) {
			$class = $this->class;
		}

		return $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
	}

}
