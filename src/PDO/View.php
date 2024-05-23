<?php

namespace Katu\PDO;

use App\Classes\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Options\OptionCollection;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;

class View extends Table
{
	protected function getCacheTableNameSeparator(): string
	{
		return "__";
	}

	protected function getCacheTableNamePrefix(): string
	{
		return "_c";
	}

	protected function generateCacheTableNameDate(): string
	{
		return (new Time)->format("YmdHis");
	}

	protected function getCacheTableNameDateRandomLength(): int
	{
		return 4;
	}

	protected function generateCacheTableNameDateRandom(): string
	{
		return \Katu\Tools\Random\Generator::getIdString($this->getCacheTableNameDateRandomLength());
	}

	protected function generateCacheTableNameSuffix(): string
	{
		return implode([
			$this->generateCacheTableNameDate(),
			$this->generateCacheTableNameDateRandom(),
		]);
	}

	protected function getCacheTableNamePlainAvailableLength(): int
	{
		return $this->getMaxTableNameLength() - mb_strlen($this->composeCacheTableName());
	}

	protected function getCacheTableNamePlainFits(): bool
	{
		return mb_strlen($this->getName()->getPlain()) <= $this->getCacheTableNamePlainAvailableLength();
	}

	protected function getCacheTableNamePlainHash(): string
	{
		return hash("crc32", $this->getName()->getPlain());
	}

	protected function getCacheTableNamePlainHashLength(): int
	{
		return mb_strlen($this->getCacheTableNamePlainHash());
	}

	protected function composeCacheTableName(?string $plain = null, ?string $plainHash = null): string
	{
		return implode([
			$this->getCacheTableNamePrefix(),
			$this->getCacheTableNameSeparator(),
			$plain,
			$plainHash,
			$this->getCacheTableNameSeparator(),
			$this->generateCacheTableNameSuffix(),
		]);
	}

	protected function generateCacheTableName(): Name
	{
		if ($this->getCacheTableNamePlainFits()) {
			return new Name($this->composeCacheTableName($this->getName()->getPlain()));
		} else {
			return new Name($this->composeCacheTableName(substr($this->getName()->getPlain(), 0, $this->getCacheTableNamePlainAvailableLength() - $this->getCacheTableNamePlainHashLength()), $this->getCacheTableNamePlainHash()));
		}
	}

	protected function getCacheStatusFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), ...(new TIdentifier("databases", $this->getConnection()->getName(), "views", $this->getName(), "cache_status"))->getPathParts());
	}

	protected function setCacheStatus(CacheStatus $cacheStatus): View
	{
		$this->getCacheStatusFile()->set(serialize($cacheStatus));

		return $this;
	}

	protected function getCacheStatus(): CacheStatus
	{
		$response = @unserialize($this->getCacheStatusFile()->get());
		if ($response instanceof CacheStatus) {
			return $response;
		}

		return new CacheStatus($this->getName());
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

	public function getOrCreateCacheTable(Timeout $timeout, ?OptionCollection $options = null): ?Table
	{
		$this->cacheIfIsExpired($timeout, $options);

		return $this->getCacheTable();
	}

	public function getResolvedTable(Timeout $timeout, ?OptionCollection $options = null): Table
	{
		return $this->getOrCreateCacheTable($timeout, $options) ?: $this;
	}

	public function getIsCached(): bool
	{
		return (bool)$this->getCacheTable();
	}

	protected function getIsBeingCached(): bool
	{
		foreach ($this->getConnection()->getProcesses() as $process) {
			if (preg_match("/CREATE TABLE.+AS SELECT \* FROM.+{$this->getName()}/", $process->getInfo())) {
				return true;
			}
		}

		return false;
	}

	public function cache(?OptionCollection $options = null): bool
	{
		if (!$this->getIsBeingCached()) {
			$cacheTableName = $this->generateCacheTableName();
			try {
				if ($this->copy($this->getConnection()->getTable($cacheTableName), $options)) {
					$this->setCacheStatus($this->getCacheStatus()->setCacheTableName($cacheTableName)->setTimeCached(new Time));
					return true;
				}
			} catch (\Throwable $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);
			}
		}

		return false;
	}

	public function cacheIfIsNotCached(): bool
	{
		if ($this->getIsCached()) {
			return true;
		}

		return $this->cache();
	}

	public function getIsCacheExpired(Timeout $timeout): bool
	{
		if (!$this->getCacheTable()) {
			return true;
		}

		$timeCached = $this->getCacheStatus()->getTimeCached();
		if (!$timeCached) {
			return true;
		}

		return !$timeCached->fitsInTimeout($timeout);
	}

	public function getIsCacheFresh(Timeout $timeout): bool
	{
		return !$this->getIsCacheExpired($timeout);
	}

	public function cacheIfIsExpired(Timeout $timeout, ?OptionCollection $options = null): bool
	{
		if (!$this->getIsCacheExpired($timeout)) {
			return true;
		}

		return $this->cache($options);
	}

	public function getModels(): array
	{
		return array_values(array_filter(array_map(function (TClass $class) {
			if ($class->getName()::TABLE == $this->getName()->getPlain()) {
				return $class;
			}
		}, \Katu\Models\View::getClasses())));
	}

	public function getTimeCached(): ?Time
	{
		return $this->getCacheStatus()->getTimeCached();
	}

	public function getCreateSyntax(): string
	{
		$sql = " SHOW CREATE TABLE {$this->getName()} ";
		$res = $this->getConnection()->createQuery($sql)->getResult();

		return $res[0]["Create View"];
	}
}
