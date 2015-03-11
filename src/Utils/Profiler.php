<?php

namespace Katu\Utils;

class Profiler {

	static $queries = [];

	static function isOn() {
		return \Katu\App::isProfilerOn();
	}

	static function addQuery() {
		if (@func_get_arg(0) instanceof ProfilerQuery) {
			return static::$queries[] = func_get_arg(0);
		} else {
			return static::$queries[] = new ProfilerQuery(func_get_arg(0), func_get_arg(1));
		}
	}

	static function getPath() {
		return \Katu\Utils\Tmp::getPath(['!profiler', \Katu\Utils\Url::getCurrent(), '!' . (new \Katu\Utils\DateTime())->format('Y-m-d-H-i-s') . '-' . \Katu\Utils\DateTime::getMicroseconds()]);
	}

	static function dump() {
		if (static::isOn()) {
			$csv = CSV::setFromAssoc(static::getQueriesAsArray(), [
				'delimiter' => ';',
			]);
			$csv->save(static::getPath());

			return true;
		}

		return false;
	}

	static function getQueriesAsArray() {
		$array = [];

		foreach (static::$queries as $query) {
			$array[] = [
				'duration' => $query->duration,
				'query'    => $query->query,
			];
		}

		return $array;
	}

}
