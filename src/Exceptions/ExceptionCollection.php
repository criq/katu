<?php

namespace Katu\Exceptions;

class ExceptionCollection extends Exception implements \ArrayAccess, \Iterator, \Countable
{
	protected $iteratorPosition = 0;
	public $collection = [];

	public function add(): ExceptionCollection
	{
		return $this->addException(...func_get_args());
	}

	public function addException(\Exception $exception): ExceptionCollection
	{
		if ($exception instanceof ExceptionCollection) {
			foreach ($exception as $e) {
				$this->collection[] = $e;
			}
		} else {
			$this->collection[] = $exception;
		}

		return $this;
	}

	public function has(): bool
	{
		return $this->hasExceptions();
	}

	public function hasExceptions(): bool
	{
		return (bool) $this->countExceptions();
	}

	public function countExceptions(): int
	{
		return (int)count($this->collection);
	}

	public function getErrorNames(): array
	{
		$errorNames = [];
		foreach ($this->collection as $exception) {
			$errorNames = array_merge($errorNames, $exception->getErrorNames());
		}

		return array_values(array_filter(array_unique($errorNames)));
	}

	public function replaceErrorName(string $errorName, string $replacement): ExceptionCollection
	{
		foreach ($this->collection as $exception) {
			$exception->replaceErrorName($errorName, $replacement);
		}

		return $this;
	}

	public function getResponseArray(): array
	{
		return [
			'errors' => array_map(function ($e) {
				return $e->getResponseArray();
			}, $this->collection),
		];
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->collection[] = $value;
		} else {
			$this->collection[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->collection[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
	}

	/****************************************************************************
	 * Iterator.
	 */
	public function rewind()
	{
		$this->iteratorPosition = 0;
	}

	public function current()
	{
		return $this->collection[$this->iteratorPosition];
	}

	public function key()
	{
		return $this->iteratorPosition;
	}

	public function next()
	{
		++$this->iteratorPosition;
	}

	public function valid()
	{
		return isset($this->collection[$this->iteratorPosition]);
	}

	/****************************************************************************
	 * Countable.
	 */
	public function count()
	{
		return count($this->collection);
	}
}
