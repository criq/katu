<?php

namespace Katu\Tools\Images;

abstract class Filter {

	protected $params = [];

	abstract public function apply($image);

	public function __construct($params = []) {
		$this->params = $params;
	}

	static function createByCode($code) {
		$class = '\\Katu\\Tools\\Images\\Filters\\' . ucfirst($code);

		return new $class;
	}

	public function getCode() {
		return lcfirst(array_slice(explode('\\', get_class($this)), -1)[0]);
	}

	public function setParams($params) {
		$this->params = $params;

		return $this;
	}

	public function getArray() {
		return array_merge([
			'filter' => $this->getCode(),
		], $this->params);
	}

}
