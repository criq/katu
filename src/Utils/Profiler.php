<?php

namespace Katu\Utils;

class Profiler {

	static $profilers = [];

	public $stopwatch;
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
		$path = \Katu\Utils\Tmp::getPath(['!profiler', \Katu\Utils\Url::getCurrent(), '!' . (new \Katu\Utils\DateTime())->format('Y-m-d-H-i-s') . '-' . \Katu\Utils\DateTime::getMicroseconds()]);
		$path = dirname($path) . '/' . ltrim(basename($path), '.') . '.csv';

		return $path;
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

	static function init($name) {
		if (static::isOn()) {
			if (!isset(static::$profilers[$name])) {
				static::$profilers[$name] = new static;
			}

			return static::$profilers[$name];
		}

		return false;
	}

	static function get($name) {
		if (static::isOn()) {
			return static::init($name);
		}

		return false;
	}

	static function dump($name) {
		if (static::isOn()) {
			$profiler = static::get($name);
			$csv = CSV::setFromAssoc($profiler->getQueriesAsArray(), [
				'delimiter' => ';',
			]);
			$csv->save($profiler->getPath());

			return true;
		}

		return false;
	}

	static function reset($name) {
		unset(static::$profilers[$name]);
	}

}
