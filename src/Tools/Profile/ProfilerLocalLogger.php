<?php

namespace Katu\Tools\Profile;

/**
 * Append-only local sink for profiler output. Does not use application loggers (no cloud, no Monolog stack).
 *
 * Default path: sys_get_temp_dir()/katu-profiler.log. Use {@see createForStderr()} for Docker/CLI.
 */
class ProfilerLocalLogger
{
	protected $path;

	public function __construct(?string $path = null)
	{
		$this->path = $path ?? (rtrim(sys_get_temp_dir(), "/") . "/katu-profiler.log");
	}

	public static function createForStderr(): ProfilerLocalLogger
	{
		return new static("php://stderr");
	}

	public function setPath(string $path): ProfilerLocalLogger
	{
		$this->path = $path;

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function logProfiler(Profiler $profiler, string $title = "profile"): ProfilerLocalLogger
	{
		return $this->logLine($profiler->format($title));
	}

	public function logLine(string $line): ProfilerLocalLogger
	{
		@file_put_contents($this->path, $line . "\n", FILE_APPEND | LOCK_EX);

		return $this;
	}
}
