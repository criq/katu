<?php

namespace Katu\Tools\Profile;

use Katu\Tools\Calendar\Seconds;

/**
 * Collects PDO queries executed while {@see ProfilerContext} is active.
 * {@see \Katu\PDO\Query::getResult()} records each statement; call
 * {@see self::appendSummaryToProfiler()} before formatting the request summary and
 * {@see self::flushDetailsToLogger()} for a per-query detail log.
 */
class ProfilerSqlLog
{
	private const MAX_SQL_LENGTH = 800;

	/** @var bool */
	private static $enabled = false;

	/** @var list<array{connection: string, sql: string, duration: float}> */
	private static $entries = [];

	public static function enable(): void
	{
		self::$enabled = true;
		self::$entries = [];
	}

	public static function reset(): void
	{
		self::$enabled = false;
		self::$entries = [];
	}

	public static function isEnabled(): bool
	{
		return self::$enabled && ProfilerContext::isActive();
	}

	public static function record(string $connectionTitle, string $sql, float $durationSeconds): void
	{
		if (!self::isEnabled()) {
			return;
		}

		self::$entries[] = [
			"connection" => $connectionTitle,
			"sql" => self::normalizeSql($sql),
			"duration" => $durationSeconds,
		];
	}

	/**
	 * @return list<array{connection: string, sql: string, duration: float}>
	 */
	public static function getEntries(): array
	{
		return self::$entries;
	}

	public static function getCount(): int
	{
		return count(self::$entries);
	}

	public static function getTotalDuration(): float
	{
		$total = 0.0;
		foreach (self::$entries as $entry) {
			$total += $entry["duration"];
		}

		return $total;
	}

	public static function getSummaryLapName(): string
	{
		return "sql[" . self::getCount() . "]=" . round(self::getTotalDuration(), 4) . "s";
	}

	public static function appendSummaryToProfiler(?Profiler $profiler): void
	{
		if (!$profiler || self::getCount() === 0) {
			return;
		}

		$profiler->addMeasured(self::getSummaryLapName(), new Seconds(self::getTotalDuration()));
	}

	public static function flushDetailsToLogger(ProfilerLocalLogger $logger, string $title): void
	{
		if (self::getCount() === 0) {
			return;
		}

		$logger->logLine("[" . $title . " sql] " . self::getCount() . " queries, " . round(self::getTotalDuration(), 4) . "s total");
		foreach (self::$entries as $index => $entry) {
			$logger->logLine(sprintf(
				"  #%d %s %0.4fs %s",
				$index + 1,
				$entry["connection"],
				$entry["duration"],
				$entry["sql"]
			));
		}
	}

	protected static function normalizeSql(string $sql): string
	{
		$sql = preg_replace("/[\r\n\t]+/", " ", $sql);
		$sql = preg_replace("/\s{2,}/", " ", trim($sql));
		if (mb_strlen($sql) > self::MAX_SQL_LENGTH) {
			$sql = mb_substr($sql, 0, self::MAX_SQL_LENGTH) . "…";
		}

		return $sql;
	}
}
