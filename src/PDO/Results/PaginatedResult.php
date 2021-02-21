<?php

namespace Katu\PDO\Results;

class PaginatedResult extends Result
{
	protected $pagination;

	public function __construct(\Katu\PDO\Connection $connection, \PDOStatement $statement, \Katu\Interfaces\Factory $factory, ?\Sexy\Page $page = null)
	{
		parent::__construct(...func_get_args());

		// Set default page if empty.
		if (!$page) {
			$page = new \Sexy\Page(1, $this->getTotal() ?: 1);
		}

		$this->setPagination(new \Katu\Types\TPagination($this->getTotal(), $page->perPage, $page->page));
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

	public function setTotal(int $total) : PaginatedResult
	{
		parent::setTotal(...func_get_args());

		try {
			$this->getPagination()->setTotal($total);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return $this;
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

	/****************************************************************************
	 * REST.
	 */
	public function getResponseArray()
	{
		$res = [];
		$res['pagination'] = $this->getPagination()->getResponseArray();

		foreach ($this as $object) {
			$res['items'][] = $object->getResponseArray();
		}

		return $res;
	}
}
