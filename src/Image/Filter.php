<?php

namespace Katu\Image;

abstract class Filter {

	protected $params = [];

	abstract public function apply($image);

	public function __construct($params = []) {
		$this->params = $params;
	}

	public function getCode() {
		return lcfirst(array_slice(explode('\\', get_class($this)), -1)[0]);
	}

	public function getArray() {
		return array_merge([
			'filter' => $this->getCode(),
		], $this->params);
	}

}
