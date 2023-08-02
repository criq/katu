<?php

namespace Katu\Tools\Session;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Cookies\Cookie;
use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;

class Session
{
	const DEFAULT_COOKIE_LIFETIME = "1 year";
	const DEFAULT_NAME = "session";

	public function __construct()
	{
		static::initialize();
	}

	/****************************************************************************
	 * General cookie methods.
	 */
	public static function getCookieOptions(): OptionCollection
	{
		try {
			$config = \Katu\Config\Config::get("app", "cookie");
		} catch (\Throwable $e) {
			$config = [];
		}

		return Cookie::getDefaultOptions()->getMergedWith(OptionCollection::createFromArray($config));
	}

	public static function setCookieParams(): bool
	{
		$options = static::getCookieOptions();

		return session_set_cookie_params(
			$options->getValue("LIFETIME"),
			$options->getValue("PATH"),
			$options->getValue("DOMAIN"),
			$options->getValue("SECURE"),
			$options->getValue("HTTP_ONLY"),
		);
	}

	/****************************************************************************
	 * Session cookie methods.
	 */
	public static function getPath(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), "session");
	}

	public static function makePath(): bool
	{
		try {
			return static::getPath()->makeDir();
		} catch (\Throwable $e) {
			return false;
		}
	}

	public static function getDefaultOptions(): OptionCollection
	{
		$defaultCookieLifetime = static::DEFAULT_COOKIE_LIFETIME;

		return new OptionCollection([
			new Option("COOKIE_LIFETIME", abs((string)(new Time("+ {$defaultCookieLifetime}"))->getAge())),
			new Option("NAME", static::DEFAULT_NAME),
			new Option("SAVE_PATH", (string)static::getPath()),
		]);
	}

	public static function getOptions(): OptionCollection
	{
		try {
			$config = \Katu\Config\Config::get("app", "session");
		} catch (\Throwable $e) {
			$config = [];
		}

		return static::getDefaultOptions()->getMergedWith(OptionCollection::createFromArray($config));
	}

	public static function getConfig(): array
	{
		$res = [];
		foreach (static::getOptions() as $option) {
			$res[mb_strtolower($option->getCode()->getConstantFormat())] = $option->getValue();
		}

		return $res;
	}

	public static function initialize(): bool
	{
		if (!session_id()) {
			static::setCookieParams();
			static::makePath();
			session_start(static::getConfig());
		}

		return true;
	}

	/****************************************************************************
	 * Manipulation methods.
	 */
	public function getContents(): array
	{
		return (array)$_SESSION;
	}

	public function setKey(string $key, $value): Session
	{
		$_SESSION[$key] = $value;

		return $this;
	}

	public function getKey(string $key)
	{
		return $_SESSION[$key] ?? null;
	}

	public function unsetKey(string $key): Session
	{
		unset($_SESSION[$key]);

		return $this;
	}

	public function getLibraries(): LibraryCollection
	{
		$res = new LibraryCollection;
		foreach ($this->getContents() as $key => $value) {
			if ($value instanceof Library) {
				$res[] = $value;
			}
		}

		return $res;
	}

	public function getVariableLibrary(?string $key = null): VariableLibrary
	{
		if (!$key) {
			$key = VariableLibrary::KEY;
		}

		if (!($this->getKey($key) instanceof VariableLibrary)) {
			$this->setKey($key, new VariableLibrary($key));
		}

		return $this->getKey($key);
	}

	public function setVariable(string $key, $value): Session
	{
		$this->getVariableLibrary()->setVariable($key, $value);

		return $this;
	}

	public function getVariable(string $key)
	{
		return $this->getVariableLibrary()->getVariable($key);
	}

	public function getVariables(): VariableLibrary
	{
		return $this->getVariableLibrary();
	}

	public function unsetVariable(string $key): Session
	{
		$this->getVariableLibrary()->unsetVariable($key);

		return $this;
	}

	public function getFlashLibrary(?string $key = null): FlashLibrary
	{
		if (!$key) {
			$key = FlashLibrary::KEY;
		}

		if (!($this->getKey($key) instanceof FlashLibrary)) {
			$this->setKey($key, new FlashLibrary($key));
		}

		return $this->getKey($key);
	}

	public function addFlash(Flash $flash): Session
	{
		$this->getFlashLibrary()->addFlash($flash);

		return $this;
	}

	public function getFlashes(): FlashLibrary
	{
		return $this->getFlashLibrary();
	}
}
