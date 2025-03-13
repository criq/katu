<?php

namespace Katu\Tools\Session;

use App\Config\CookieConfig;

class Session
{
	public function __construct()
	{
		if (!session_id()) {
			session_start($this->getOptions());
		}
	}

	public function getOptions(): array
	{
		$config = new CookieConfig;

		return [
			"cookie_domain" => $config->getDomain(),
			"cookie_httponly" => $config->getIsHTTPOnly(),
			"cookie_lifetime" => $config->getLifetime(),
			"cookie_path" => $config->getPath(),
			"cookie_secure" => $config->getIsSecure(),
			"gc_maxlifetime" => $config->getLifetime(),
			"save_path" => (string)static::getStorage()->getPath(),
			"use_cookies" => true,
			"use_only_cookies" => true,
			"use_strict_mode" => true,
		];
	}

	/****************************************************************************
	 * Session cookie methods.
	 */
	public function getStorage(): \Katu\Files\File
	{
		$storage = new \Katu\Files\File(\App\App::getTemporaryDir(), "session");
		$storage->makeDir();

		return $storage;
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
