<?php

namespace Katu\Tools\Session;

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
		$class = \App\App::getContainer()->get(\Katu\Config\CookieConfig::class);
		$cookieConfig = new $class;

		$class = \App\App::getContainer()->get(\Katu\Config\SessionConfig::class);
		$sessionConfig = new $class;

		$options = [
			"cookie_domain" => $cookieConfig->getDomain(),
			"cookie_httponly" => $cookieConfig->getIsHTTPOnly(),
			"cookie_lifetime" => $cookieConfig->getLifetime(),
			"cookie_path" => $cookieConfig->getPath(),
			"cookie_secure" => $cookieConfig->getIsSecure(),
			"gc_maxlifetime" => $cookieConfig->getLifetime(),
			"name" => $sessionConfig->getName(),
			"use_cookies" => true,
			"use_only_cookies" => true,
			"use_strict_mode" => true,
		];

		// Only set save_path for file-based sessions
		// This allows php.ini to configure Redis or other session handlers
		if (ini_get("session.save_handler") === "files") {
			$options["save_path"] = (string)static::getStorage()->getPath();
		}

		return $options;
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
