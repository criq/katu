<?php

namespace Katu\Tools\Profile;

use Katu\Tools\Calendar\Seconds;

/**
 * Holds the active {@see Profiler} for the current request (or CLI invocation).
 * Controllers set it at the start; models and Twig call static helpers; clear at the end to avoid leaking state across FPM workers.
 */
class ProfilerContext
{
	/** @var Profiler|null */
	private static $profiler;

	public static function set(?Profiler $profiler)
	{
		self::$profiler = $profiler;
		if ($profiler) {
			ProfilerSqlLog::enable();
		} else {
			ProfilerSqlLog::reset();
		}
	}

	public static function get(): ?Profiler
	{
		return self::$profiler;
	}

	public static function clear()
	{
		self::$profiler = null;
		ProfilerSqlLog::reset();
	}

	public static function isActive()
	{
		return self::$profiler instanceof Profiler;
	}

	public static function lap(string $name)
	{
		if (self::$profiler) {
			self::$profiler->lap($name);
		}
	}

	/**
	 * @template T
	 * @param callable(): T $callback
	 * @return T|null
	 */
	public static function segment(string $name, callable $callback)
	{
		if (!self::$profiler) {
			return $callback();
		}

		return self::$profiler->segment($name, $callback);
	}

	/**
	 * Used by compiled Twig {% profile %} nodes; $name may be any expression coercible to string.
	 */
	public static function addMeasuredSegment($name, float $seconds)
	{
		if (!self::$profiler) {
			return;
		}

		self::$profiler->addMeasured((string) $name, new Seconds($seconds));
	}
}
