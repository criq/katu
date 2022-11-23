<?php

namespace Katu\Exceptions;

use ArrayAccess;
use Countable;
use Iterator;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionCollection extends Exception implements ArrayAccess, Iterator, Countable, RestResponseInterface
{
	protected $iteratorPosition = 0;
	protected $storage = [];

	public function addException(\Exception $exception): ExceptionCollection
	{
		if ($exception instanceof ExceptionCollection) {
			foreach ($exception as $e) {
				$this->storage[] = $e;
			}
		} else {
			$this->storage[] = $exception;
		}

		return $this;
	}

	public function add(): ExceptionCollection
	{
		return $this->addException(...func_get_args());
	}

	public function hasExceptions(): bool
	{
		return (bool) $this->countExceptions();
	}

	public function has(): bool
	{
		return $this->hasExceptions();
	}

	public function countExceptions(): int
	{
		return (int)count($this->storage);
	}

	public function getErrorNames(): array
	{
		$errorNames = [];
		foreach ($this->storage as $exception) {
			$errorNames = array_merge($errorNames, $exception->getErrorNames());
		}

		return array_values(array_filter(array_unique($errorNames)));
	}

	public function replaceErrorName(string $errorName, string $replacement): ExceptionCollection
	{
		foreach ($this->storage as $exception) {
			$exception->replaceErrorName($errorName, $replacement);
		}

		return $this;
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->storage[] = $value;
		} else {
			$this->storage[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->storage[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->storage[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->storage[$offset]) ? $this->storage[$offset] : null;
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
		return $this->storage[$this->iteratorPosition];
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
		return isset($this->storage[$this->iteratorPosition]);
	}

	/****************************************************************************
	 * Countable.
	 */
	public function count()
	{
		return count($this->storage);
	}

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
	{
		return new RestResponse([
			"errors" => array_map(function ($e) {
				return $e->getRestResponse();
			}, $this->storage),
		]);
	}
}
