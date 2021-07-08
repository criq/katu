<?php

namespace Katu\PDO;

use Katu\Types\TPagination;

class Result extends \ArrayObject
{
	protected $error;
	protected $pagination;

	public function __construct(?Query $query = null)
	{
		$this->setQuery($query);
	}

	public function setQuery(?Query $query)
	{
		$this->query = $query;

		return $this;
	}

	public function getQuery() : Query
	{
		return $this->query;
	}

	public function setError($error) : Result
	{
		$this->error = $error;

		return $this;
	}

	public function getError()
	{
		return $this->error;
	}

	public function setPagination(TPagination $pagination) : Result
	{
		$this->pagination = $pagination;

		return $this;
	}

	public function getPagination() : TPagination
	{
		return $this->pagination;
	}

	public function getItems() : array
	{
		return $this->getArrayCopy();
	}

	public function getOne()
	{
		return $this[0] ?? null;
	}

	public function each($callback)
	{
		$res = [];
		foreach ($this->getItems() as $item) {
			if (is_string($callback) && method_exists($item, $callback)) {
				$res[] = call_user_func_array([$item, $callback], [$item]);
			} else {
				$res[] = call_user_func_array($callback, [$item]);
			}
		}

		return $res;
	}

	public function getColumnValues($column) : array
	{
		$values = [];
		foreach ($this->getItems() as $item) {
			if (is_object($item)) {
				$values[] = $item->$column;
			} else {
				$values[] = $item[$column];
			}
		}

		return $values;
	}

	public function getPage() : int
	{
		return $this->getPagination()->getPage();
	}

	public function getPerPage() : int
	{
		return $this->getPagination()->getPerPage();
	}

	public function getPages() : int
	{
		return $this->getPagination()->getPages();
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
			$res['items'][] = $object->getResponseArray(...func_get_args());
		}

		return $res;
	}
}
