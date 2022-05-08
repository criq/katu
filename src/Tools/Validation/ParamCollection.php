<?php

namespace Katu\Tools\Validation;

class ParamCollection extends \ArrayObject
{
	public function __construct(?array $params = [])
	{
		foreach ($params as $param) {
			$this->append($param);
		}
	}

	public function offsetSet($key, $value)
	{
		parent::offsetSet($key ?: $value->getKey(), $value);
	}

	public function addParamCollection(ParamCollection $params): ParamCollection
	{
		foreach ($params as $param) {
			$this->append($param);
		}

		return $this;
	}

	public function getAliasArray(): array
	{
		$array = [];
		foreach ($this as $param) {
			$array[$param->getAlias()] = $param;
		}

		return $array;
	}

	public function getResponseArray(): array
	{
		return array_map(function (Param $param) {
			return $param->getResponseArray();
		}, $this->getAliasArray());
	}

	public function filterWithoutKeys(array $keys): ParamCollection
	{
		$res = new static;
		foreach ($this as $param) {
			if (!in_array($param->getKey(), $keys)) {
				$res[] = $param;
			}
		}

		return $res;
	}
}
