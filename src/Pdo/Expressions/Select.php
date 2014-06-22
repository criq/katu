<?php

namespace Katu\Pdo\Expressions;

class Select extends \Katu\Pdo\Expression {

	public $select  = array();
	public $from    = array();
	public $join    = array();
	public $where   = array();
	public $groupBy = array();
	public $orderBy = array();

	private $_optGetTotalRows = TRUE;
	private $_optPage;

	public function __construct($select = NULL) {
		if ($select) {
			$this->select($select);
		}

		return $this;
	}

	public function select() {
		if (
			count(func_get_args()) == 1
			&& func_get_arg(0) instanceof \Katu\Pdo\Table
		) {

			$this->select[] = new \Katu\Pdo\Column(func_get_arg(0), '*');

		} elseif (
			count(func_get_args()) == 1
			&& func_get_arg(0) instanceof \Katu\Pdo\Table
		) {

			$this->select[] = func_get_arg(0);

		} elseif (
			count(func_get_args()) == 1
		) {

			$this->select[] = func_get_arg(0);

		}

		return $this;
	}

	public function from($from) {
		$this->from[] = $from;

		return $this;
	}

	public function join() {
		if (
			count(func_get_args()) == 1
			&& func_get_arg(0) instanceof \Katu\Pdo\Expression
		) {

			$this->join[] = func_get_arg(0);

		} elseif (
			count(func_get_args()) == 2
			&& func_get_arg(0) instanceof \Katu\Pdo\Column
			&& func_get_arg(1) instanceof \Katu\Pdo\Column
		) {

			$this->join[] = new Join(func_get_arg(0)->table, new CmpEq(func_get_arg(0), func_get_arg(1)));

		} elseif (
			count(func_get_args()) == 4
			&& func_get_arg(0) instanceof \Katu\Pdo\Table
			&& is_string(func_get_arg(1))
			&& func_get_arg(2) instanceof \Katu\Pdo\Table
			&& is_string(func_get_arg(3))
		) {

			$this->join[] = new Join(func_get_arg(0), new CmpEq(new Column(func_get_arg(0), func_get_arg(1)), new Column(func_get_arg(2), func_get_arg(3))));

		} else {

			throw new \Katu\Exceptions\PdoExpressionErorException("Invalid arguments passed to expression.");

		}

		return $this;
	}

	public function where() {
		if (
			count(func_get_args() == 2)
			&& func_get_arg(0) instanceof \Katu\Pdo\Column
			&& in_array(gettype(func_get_arg(1)), array('string', 'int'))
		) {

			$this->where[] = new CmpEq(func_get_arg(0), new BindValue(NULL, func_get_arg(1)));

		} elseif (
			count(func_get_args()) == 1
		) {

			$this->where[] = func_get_arg(0);

		} else {

			throw new \Katu\Exceptions\PdoExpressionErorException("Invalid arguments passed to expression.");

		}

		return $this;
	}

	public function groupBy($groupBy) {
		$this->groupBy[] = $groupBy;

		return $this;
	}

	public function orderBy($orderBy) {
		$this->orderBy[] = $orderBy;

		return $this;
	}

	public function setPage($page) {
		$this->_optPage = $page;

		return $this;
	}

	public function setOptions($options = array()) {
		if (isset($options['select'])) {
			$this->select($options['select']);
		}

		if (isset($options['groupBy'])) {
			$this->groupBy($options['groupBy']);
		}

		if (isset($options['orderBy'])) {
			$this->orderBy($options['orderBy']);
		}

		if (isset($options['page'])) {
			$this->setPage($options['page']);
		}

		return $this;
	}

	public function getSql(&$context = array()) {
		$sql = " SELECT ";

		if ($this->_optGetTotalRows) {
			$sql .= " SQL_CALC_FOUND_ROWS ";
		}

		if ($this->select) {
			$sql .= implode(", ", array_map(function($i) use(&$context) {
				return $i->getSql($context);
			}, $this->select));
		} else {
			$sql .= " * ";
		}

		if ($this->from) {
			$sql .= " FROM " . implode(", ", array_map(function($i) use(&$context) {
				return $i->getSql($context);
			}, $this->from));
		}

		if ($this->join) {
			$sql .= implode(" ", array_map(function($i) use(&$context) {
				return $i->getSql($context);
			}, $this->join));
		}

		if ($this->where) {
			$sql .= " WHERE " . implode(" AND ", array_map(function($i) use(&$context) {
				return $i->getSql($context);
			}, $this->where));
		}

		if ($this->groupBy) {
			$sql .= " GROUP BY " . implode(", ", array_map(function($i) use(&$context) {
				return $i->getSql($context);
			}, $this->groupBy));
		}

		if ($this->orderBy) {
			$sql .= " ORDER BY " . implode(", ", array_map(function($i) use(&$context) {
				return $i->getSql($context);
			}, $this->orderBy));
		}

		if ($this->_optPage) {
			$sql .= " LIMIT " . $this->_optPage->getSql($context);
		}

		return $sql;
	}

	public function getBindValues() {
		$this->getSql($context);

		return isset($context['bindValues']) ? (array) $context['bindValues'] : array();
	}

	public function getPage() {
		return $this->_optPage;
	}

}
