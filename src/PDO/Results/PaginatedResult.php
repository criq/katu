<?php

namespace Katu\PDO\Results;

class PaginatedResult extends Result
{
	public function __construct(\Katu\PDO\Connection $connection, \PDOStatement $statement, \Katu\Interfaces\Factory $factory, ?\Sexy\Page $page = null)
	{
		parent::__construct($connection, $statement, $factory);

		if (strpos($this->getStatement()->queryString, 'SQL_CALC_FOUND_ROWS')) {
			$sql = " SELECT FOUND_ROWS() AS total ";
			$total = (int)$this->getConnection()->createQuery($sql)->getResult()->getItems()[0]['total'];
		} else {
			$total = (int)$this->getStatement()->rowCount();
		}

		// Set default page if empty.
		if (!$page) {
			$page = new \Sexy\Page(1, $total ?: 1);
		}

		$this->setPagination(new \Katu\Types\TPagination($total, $page->perPage, $page->page));
	}

	public function setPagination(\Katu\Types\TPagination $pagination)
	{
		$this->pagination = $pagination;

		return $this;
	}

	public function getPagination()
	{
		return $this->pagination;
	}

	public function getTotal()
	{
		return $this->getPagination()->total;
	}

	public function getPage()
	{
		return $this->getPagination()->page;
	}

	public function getPerPage()
	{
		return $this->getPagination()->perPage;
	}

	public function getPages()
	{
		return $this->getPagination()->pages;
	}
}
