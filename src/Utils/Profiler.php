<?php

namespace Katu\Utils;

class Profiler {

	static $profilers = [];

	public $queries = [];

	public function __construct() {
		$this->stopwatch = new Stopwatch;
	}

	static function isOn() {
		return \Katu\App::isProfilerOn();
	}

	static function add() {
		if (static::isOn()) {
			if (@func_get_arg(0) instanceof Profiler\Query) {
				foreach (static::$profilers as $profiler) {
					$profiler->addQuery(func_get_arg(0));
				}
			}
		}

		return false;
	}

	public function addQuery($query) {
		return $this->queries[] = $query;
	}

	public function getPath() {
		return \Katu\Utils\Tmp::getPath(['!profiler', \Katu\Utils\Url::getCurrent(), '!' . (new \Katu\Utils\DateTime())->format('Y-m-d-H-i-s') . '-' . \Katu\Utils\DateTime::getMicroseconds()]);
	}

	public function getQueriesAsArray() {
		$array = [];

		foreach ($this->queries as $query) {
			$array[] = [
				'duration' => $query->duration,
				'query'    => $query->query,
			];
		}

		return $array;
	}

	static function initGlobal() {
		if (static::isOn()) {
			if (!isset(static::$profilers['global'])) {
				static::$profilers['global'] = new static;
			}

			return static::$profilers['global'];
		}

		return false;
	}

	static function getGlobal() {
		if (static::isOn()) {
			return static::initGlobal();
		}

		return false;
	}

	static function dumpGlobal() {
		if (static::isOn()) {
			$profiler = static::getGlobal();
			$csv = CSV::setFromAssoc($profiler->getQueriesAsArray(), [
				'delimiter' => ';',
			]);
			$csv->save($profiler->getPath());

			return true;
		}

		return false;
	}

}
