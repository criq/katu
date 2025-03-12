<?php

namespace Katu\Tools\Images;

abstract class Filter
{
	protected $params = [];

	abstract public function apply(\Intervention\Image\Image $image): bool;

	public function __construct(array $params = [])
	{
		$this->setParams($params);
	}

	public function setParams(array $params): Filter
	{
		$this->params = $params;

		return $this;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public function getArray(): array
	{
		return array_merge([
			"filter" => static::class,
		], $this->getParams());
	}
}
