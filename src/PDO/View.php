<?php

namespace Katu\PDO;

use App\Classes\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class View extends Table
{
	public function getCacheStatusFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), ...(new TIdentifier("databases", $this->getConnection()->getName(), "views", $this->getName(), "cache_status"))->getPathParts());
	}

	public function getIsBeingCached(): bool
	{
		foreach ($this->getConnection()->getProcesses() as $process) {
			if (preg_match("/CREATE TABLE.+AS SELECT \* FROM.+{$this->getName()}/", $process->getInfo())) {
				return true;
			}
		}

		return false;
	}

	public function generateCacheTableName(): Name
	{
		// hash

		// use Hidehalo\Nanoid\Client;
		// use Hidehalo\Nanoid\GeneratorInterface;

		// $client = new Client();

		// # default random generator
		// echo $client->generateId($size = 21);

		$plain = implode("__", [
			"_c",
			$this->getName()->getPlain(),
			implode([
				(new Time)->format("YmdHis"),
				\Katu\Tools\Random\Generator::getIdString(4),
			]),
		]);

		if (mb_strlen($plain) > 64) {
			$plain = implode("__", [
				"_c",
				md5($this->getName()->getPlain()),
				implode([
					(new Time)->format("YmdHis"),
					\Katu\Tools\Random\Generator::getIdString(4),
				]),
			]);
		}

		return new Name($plain);
	}

	public function setCacheStatus(CacheStatus $cacheStatus): View
	{
		$this->getCacheStatusFile()->set(serialize($cacheStatus));

		return $this;
	}

	public function getCacheStatus()
	{
		$response = @unserialize($this->getCacheStatusFile()->get());
		if ($response instanceof CacheStatus) {
			return $response;
		}

		return new CacheStatus($this->getName());
	}

	public function getIsCached(): bool
	{
		return (bool)$this->getCacheTable();
	}

	public function getCacheTable(): ?Table
	{
		$cacheTableName = $this->getCacheStatus()->getCacheTableName();
		if (!$cacheTableName) {
			return null;
		}

		$cacheTable = $this->getConnection()->getTable($cacheTableName);
		if ($cacheTable->exists()) {
			return $cacheTable;
		}

		return null;
	}

	public function cache(): bool
	{
		if (!$this->getIsBeingCached()) {
			$cacheTableName = $this->generateCacheTableName();
			try {
				if ($this->copy($this->getConnection()->getTable($cacheTableName))) {
					$this->setCacheStatus($this->getCacheStatus()->setCacheTableName($cacheTableName)->setTimeCached(new Time));
					return true;
				}
			} catch (\Throwable $e) {
				var_dump($cacheTableName->getPlain());
				// var_dump($e->getMessage());
				// var_dump($e);
				var_dump($this);
				die;
			}
		}

		return false;
	}

	public function cacheIfNotCached(): bool
	{
		if ($this->getIsCached()) {
			return true;
		}

		return $this->cache();
	}



	public function getCreateSyntax(): string
	{
		$sql = " SHOW CREATE TABLE {$this->getName()} ";
		$res = $this->getConnection()->createQuery($sql)->getResult();

		return $res[0]["Create View"];
	}

	public function getSourceTables(): array
	{
		$tableNames = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout("1 day"), function ($table) {
			$tableNames = [];

			$sql = " EXPLAIN SELECT * FROM {$table} ";
			$res = $table->getConnection()->createQuery($sql)->getResult()->getItems();
			foreach ($res as $row) {
				if (!preg_match("/^<.+>$/", $row["table"])) {
					$tableNames[] = new \Katu\PDO\Name($row["table"]);
				}
			}

			return array_values(array_filter(array_unique($tableNames)));
		}, $this);

		$tables = [];
		foreach ($tableNames as $tableName) {
			$tables[] = new Table($this->getConnection(), $tableName);
		}

		return $tables;
	}

	public function getSourceMaterializedViewNames(): array
	{
		if (preg_match_all("/`(mv_[a-z0-9_]+)`/", $this->getCreateSyntax(), $matches)) {
			return array_values(array_unique($matches[1]));
		}

		return [];
	}

	public function getSourceViewsInMaterializedViews(): array
	{
		$views = [];
		foreach (array_filter((array) $this->getSourceMaterializedViewNames()) as $tableName) {
			$views[] = new static($this->getConnection(), new Name(preg_replace("/^mv_/", "view_", $tableName)));
		}

		return $views;
	}

	public function getModels(): array
	{
		$models = [];
		foreach (\Katu\Models\View::getAllViewClasses() as $class) {
			if ($class->getName()::TABLE == $this->getName()->getPlain()) {
				$models[] = $class;
			}
		}

		return $models;
	}
}
