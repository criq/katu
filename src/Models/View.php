<?php

namespace Katu\Models;

use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;

abstract class View extends Base
{
	const AUTO_INDICES = true;
	const CACHE = true;
	const CACHE_ADVANCE = .75;
	const CACHE_DATETIME_FORMAT = "YmdHis";
	const CACHE_TIMEOUT = 86400;
	const COMPOSITE_INDEX = true;
	const CUSTOM_INDICES = "";
	const MATERIALIZE = false;
	const MATERIALIZE_ADVANCE = 1;
	const MATERIALIZE_HOURS = "";
	const MATERIALIZE_TIMEOUT = 86400;
	const MAX_NAME_LENGTH = 64;
	const PREFIX_CACHE = "_cache";
	const SEPARATOR = "_";
	const TIMEOUT = 3600;
	const TMP_LENGTH = 8;

	public static function getTableClass(): TClass
	{
		return new TClass("Katu\PDO\Table");
	}

	public static function getViewClass(): TClass
	{
		return new TClass("Katu\PDO\View");
	}

	public static function getColumnClass(): TClass
	{
		return new TClass("Katu\PDO\Column");
	}

	public static function getViewName(): \Katu\PDO\Name
	{
		return new \Katu\PDO\Name(static::TABLE);
	}

	public static function getView(): \Katu\PDO\View
	{
		$viewClass = static::getViewClass()->getName();

		return new $viewClass(static::getConnection(), static::getViewName());
	}

	public static function getTable(): \Katu\PDO\Table
	{
		return static::CACHE ? static::getView()->getResolvedTable(new Timeout(static::CACHE_TIMEOUT), new OptionCollection([
			new Option("AUTO_INDICES", static::AUTO_INDICES),
		])) : static::getView();
	}

	public static function isMaterialized(): bool
	{
		return static::MATERIALIZE;
	}

	public static function materializedTableExists()
	{
		return in_array(static::getMaterializedTableName(), static::getConnection()->getTableNames());
	}

	public static function getMaterializeAge()
	{
		return time() - static::getLastMaterializedTime();
	}

	public static function getMaterializeExpiryRatio(): float
	{
		return static::getMaterializeAge() / static::MATERIALIZE_TIMEOUT;
	}

	public static function isMaterializeExpired($expiryRatio = 1): bool
	{
		if (!static::isMaterialized()) {
			return false;
		}

		if (!static::materializedTableExists()) {
			return true;
		}

		if (static::getMaterializeExpiryRatio() >= $expiryRatio) {
			return true;
		}

		return false;
	}

	public static function isMaterializeExpiredAdvance()
	{
		return static::isMaterializeExpired(static::MATERIALIZE_ADVANCE);
	}

	public static function isMaterializable()
	{
		if (!static::MATERIALIZE_HOURS || \Katu\Config\Env::getPlatform() == "dev") {
			return true;
		}

		return in_array((int)(new \Katu\Tools\Calendar\Time)->format("h"), explode(",", static::MATERIALIZE_HOURS));
	}

	public static function getMaterializedTable()
	{
		$tableClass = static::getTableClass()->getName();

		return new $tableClass(static::getConnection(), static::getMaterializedTableName());
	}

	public static function getMaterializedTableName()
	{
		$name = implode(static::SEPARATOR, [
			"mv",
			preg_replace("/^view_/", "", static::getViewName()->getPlain()),
		]);

		return new \Katu\PDO\Name($name);
	}

	public static function materialize()
	{
		try {
			$class = static::getClass()->getName();

			$callback = function ($class) {
				$class::materializeSourceViews();
				$class::copy($class::getView(), $class::getMaterializedTable());
				$class::updateLastMaterializedTime();

				\Katu\Cache\Runtime::clear();

				return true;
			};

			(new \Katu\Tools\Locks\Lock(new TIdentifier("databases", static::getConnection()->getConfig()->database, "views", "materialize", $class), new Timeout(static::TIMEOUT), $callback))
				->setArgs($class)
				->run()
				;
		} catch (\Katu\Exceptions\LockException $e) {
			// Nevermind.
		}
	}

	public static function materializeIfExpired()
	{
		if (static::isMaterializeExpiredAdvance()) {
			try {
				return static::materialize();
			} catch (\Throwable $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);
			}
		}
	}

	public static function getLastMaterializedTemporaryFile(): \Katu\Files\File
	{
		return new \Katu\Files\Temporary("databases", static::getConnection()->getConfig()->getDatabase(), "views", "materialized", static::TABLE);
	}

	public static function updateLastMaterializedTime()
	{
		return static::getLastMaterializedTemporaryFile()->set(microtime(true));
	}

	public static function getLastMaterializedTime()
	{
		return (float)static::getLastMaterializedTemporaryFile()->get();
	}

	public static function getClasses(): array
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__), function () {
			$dir = new \Katu\Files\File(\App\App::getAppDir(), "Models");
			if ($dir->exists()) {
				$dir->includeAllPhpFiles();
			}

			return array_values(array_filter(array_map(function ($className) {
				if (is_subclass_of($className, "Katu\Models\View") && defined("$className::TABLE") && $className::TABLE) {
					return new TClass($className);
				}
			}, get_declared_classes())));
		});
	}

	public static function cacheAndMaterializeAll(int $limit = null)
	{
		$processed = 0;

		array_map(function (TClass $class) use ($limit, $processed) {
			if ($processed < $limit) {
				try {
					$stopwatch = new \Katu\Tools\Profiler\Stopwatch;
					if ($class->getName()::cacheIfExpired()) {
						$processed++;
					}
					if ($class->getName()::isMaterializable()) {
						$class->getName()::materializeIfExpired();
					}
					$stopwatch->finish();
					\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->debug(\Katu\Files\Formats\JSON::encodeInline([
						(string)$class,
						(string)$stopwatch->getMilliDuration(),
					]));
				} catch (\Throwable $e) {
					\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);
				}
			}
		}, static::getClasses());
	}
}
