<?php

namespace Katu\PDO\Results;

class PaginatedResult extends Result
{
	protected $pagination;

	public function __construct(\Katu\PDO\Connection $connection, \PDOStatement $statement, \Katu\Interfaces\Factory $factory, \Katu\Types\TPagination $pagination)
	{
		parent::__construct(...func_get_args());

		$this->setPagination($pagination);
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

	public function getTotal() : int
	{
		return $this->getPagination()->getTotal();
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
