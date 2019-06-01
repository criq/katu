<?php

namespace Katu\PDO\Results;

class CachedClassResult implements \Iterator, \ArrayAccess {

	public $page;
	public $pagination;
	protected $iteratorPosition = 0;
	protected $iteratorArray = null;

	static function createFromClassResult($result) {
		$object = new static;
		$object->page = $result->page;
		$object->pagination = $result->pagination;
		$object->iteratorArray = $result->getObjects();

		return $object;
	}

	public function getCount() {
		return count($this->iteratorArray);
	}

	public function getTotal() {
		return $this->pagination->total;
	}

	public function getPages() {
		return $this->pagination->pages;
	}

	public function getPage() {
		return $this->pagination->page;
	}

	public function getPerPage() {
		return $this->pagination->perPage;
	}

	public function getArray() {
		return $this->iteratorArray;
	}

	/* Iterator *****************************************************************/

	public function rewind() {
		$this->iteratorPosition = 0;
	}

	public function current() {
		return $this->iteratorArray[$this->iteratorPosition];
	}

	public function key() {
		return $this->iteratorPosition;
	}

	public function next() {
		++$this->iteratorPosition;
	}

	public function valid() {
		return isset($this->iteratorArray[$this->iteratorPosition]);
	}

	/* ArrayAccess **************************************************************/

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->iteratorArray[] = $value;
		} else {
			$this->iteratorArray[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->iteratorArray[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->iteratorArray[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->iteratorArray[$offset]) ? $this->iteratorArray[$offset] : null;
	}

}
