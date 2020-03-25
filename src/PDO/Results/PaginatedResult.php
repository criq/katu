<?php

namespace Katu\PDO\Results;

class PaginatedResult extends Result
{
	public function __construct(\Katu\PDO\Connection $connection, $statement, $page)
	{
		parent::__construct($connection, $statement);

		if (strpos($this->statement->queryString, 'SQL_CALC_FOUND_ROWS')) {
			$sql = " SELECT FOUND_ROWS() AS total ";
			$total = $this->connection->createQuery($sql)->getResult()->statement->fetchColumn();
		} else {
			$total = $this->statement->rowCount();
		}

		// Set default page if empty.
		if ($page) {
			$this->page = $page;
		} else {
			$page = new \Sexy\Page(1, $total ?: 1);
		}

		$this->pagination = new \Katu\Types\TPagination($total, $page->perPage, $page->page);
	}

	public function getPageFromMeta()
	{
		foreach ($this->meta as $_meta) {
			if ($_meta instanceof \Sexy\Page) {
				return $_meta;
			}
		}

		return false;
	}

	public function getPagination()
	{
		return new \Katu\Types\TPagination($this->getTotal(), $this->getPerPage(), $this->getPage());
	}

	public function getTotal()
	{
		return $this->pagination->total;
	}

	public function getPage()
	{
		return $this->pagination->page;
	}

	public function getPerPage()
	{
		return $this->pagination->perPage;
	}

	public function getPages()
	{
		return $this->pagination->pages;
	}
}
