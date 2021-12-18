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

	public function addParamCollection(ParamCollection $paramCollection): ParamCollection
	{
		foreach ($paramCollection as $param) {
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
}