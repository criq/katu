<?php

namespace Katu\Pdo\Expressions;

class BindValue extends \Katu\Pdo\Expression {

	const ANONYMOUS_NAME_HANDLE = 'anonBindValue';
	const ANONYMOUS_NAME_PREG   = '#^anonBindValue([0-9]+)$#';

	public $name;
	public $value;

	public function __construct($name = NULL, $value = NULL) {
		$this->name = $name;
		$this->value = $value;
	}

	public function getAnonymousNames($context) {
		if (!isset($context['bindValues'])) {
			return array();
		}

		$names = array();

		foreach ($context['bindValues'] as $name => $value) {
			if (preg_match(static::ANONYMOUS_NAME_PREG, $name)) {
				$names[] = $name;
			}
		}

		return $names;
	}

	public function getFreeAnonymousId($context) {
		$ids = array();

		foreach ($this->getAnonymousNames($context) as $name) {
			preg_match(static::ANONYMOUS_NAME_PREG, $name, $match);
			$ids[] = $match[1];
		}

		if (!$ids) {
			return 1;
		}

		return max($ids) + 1;
	}

	public function getSql(&$context = array()) {
		// Anonymous assignment.
		if (is_null($this->name)) {
			$this->name = static::ANONYMOUS_NAME_HANDLE . $this->getFreeAnonymousId($context);
		}

		$context['bindValues'][$this->name] = $this->value;

		return ':' . $this->name;
	}

}
