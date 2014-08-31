<?php

namespace Katu\Pdo\Results;

use \PDO;
use \Sexy\Page;

class PaginatedResult extends Result {

	public function __construct($pdo, $statement, $page) {
		parent::__construct($pdo, $statement);

		if (strpos($this->statement->queryString, 'SQL_CALC_FOUND_ROWS')) {
			$total = $this->pdo->createQuery("SELECT FOUND_ROWS() AS total")->getResult()->statement->fetchColumn();
		} else {
			$total = count($this->statement);
		}

		// Set default page if empty.
		if ($page) {
			$this->page = $page;
		} else {
			$page = new Page(1, $total ?: 1);
		}

		$this->pagination = new \Katu\Types\TPagination($total, $page->perPage, $page->page);
	}

	public function getPageFromMeta() {
		foreach ($this->meta as $_meta) {
			if ($_meta instanceof \Sexy\Page) {
				return $_meta;
			}
		}

		return FALSE;
	}

	public function getPagination() {
		return new \Katu\Types\TPagination($this->getTotal(), $this->getPerPage(), $this->getPage());
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

	public function getPages() {
		return $this->pagination->pages;
	}

	public function getCount() {
		return count($this->statement);
	}

}
