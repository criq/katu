<?php

namespace Katu\PDO;

use Katu\Tools\Calendar\Time;

class CacheStatus
{
	protected $cacheTableName;
	protected $timeCached;
	protected $viewName;

	public function __construct(Name $viewName, ?Name $cacheTableName = null, ?Time $time = null)
	{
		$this->setViewName($viewName);
		$this->setCacheTableName($cacheTableName);
		$this->setTimeCached($time);
	}

	public function setViewName(Name $name): CacheStatus
	{
		$this->viewName = $name;

		return $this;
	}

	public function setCacheTableName(?Name $name): CacheStatus
	{
		$this->cacheTableName = $name;

		return $this;
	}

	public function getCacheTableName(): ?Name
	{
		return $this->cacheTableName;
	}

	public function setTimeCached(?Time $time): CacheStatus
	{
		$this->timeCached = $time;

		return $this;
	}

	public function getTimeCached(): ?Time
	{
		return $this->timeCached;
	}
}
