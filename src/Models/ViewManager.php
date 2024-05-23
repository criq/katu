<?php

namespace Katu\Models;

use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;
use Katu\Types\TClass;

abstract class ViewManager extends GeneralManager
{
	abstract public function getViewName(): \Katu\PDO\Name;

	public function getViewClass(): TClass
	{
		return new TClass("Katu\PDO\View");
	}

	public function getView(): \Katu\PDO\View
	{
		$viewClass = $this->getViewClass()->getName();

		return new $viewClass($this->getConnection(), $this->getViewName());
	}

	public function getTable(): \Katu\PDO\Table
	{
		return $this->getIsCached() ? $this->getView()->getResolvedTable(new Timeout($this->getCacheTimeout()), new OptionCollection([
			new Option("AUTO_INDICES", $this->getCacheHasAutoIndices()),
		])) : $this->getView();
	}

	public function getTableName(): \Katu\PDO\Name
	{
		return $this->getTable()->getName();
	}

	public function getIsCached(): bool
	{
		return true;
	}

	public function getCacheTimeout(): Timeout
	{
		return new Timeout("1 day");
	}

	public function getCacheAdvance(): float
	{
		return .75;
	}

	public function getCacheHasAutoIndices(): bool
	{
		return true;
	}
}
