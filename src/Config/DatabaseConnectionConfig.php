<?php

namespace Katu\Config;

use Katu\Types\Encryption\TEncryptedString;

abstract class DatabaseConnectionConfig extends \Katu\Config\Config
{
	protected $charset;
	protected $database;
	protected $encryptedPassword;
	protected $host;
	protected $isProfiled = false;
	protected $title;
	protected $user;

	abstract public function getSchema(): string;
	abstract public function getDriver(): string;

	public function __construct(string $title, string $host, string $user, string $plainPassword, string $database)
	{
		$this->setDatabase($database);
		$this->setHost($host);
		$this->setPlainPassword($plainPassword);
		$this->setTitle($title);
		$this->setUser($user);
	}

	public function setTitle(string $title): DatabaseConnectionConfig
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setHost(string $host): DatabaseConnectionConfig
	{
		$this->host = $host;

		return $this;
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function setUser(string $user): DatabaseConnectionConfig
	{
		$this->user = $user;

		return $this;
	}

	public function getUser(): string
	{
		return $this->user;
	}

	public function setPlainPassword(string $plainPassword): DatabaseConnectionConfig
	{
		$this->setEncryptedPassword(\Katu\Types\Encryption\TEncryptedString::encrypt($plainPassword));

		return $this;
	}

	public function getPlainPassword(): string
	{
		return $this->getEncryptedPassword()->getOriginal();
	}

	public function setEncryptedPassword(TEncryptedString $encryptedPassword): DatabaseConnectionConfig
	{
		$this->encryptedPassword = $encryptedPassword;

		return $this;
	}

	public function getEncryptedPassword(): TEncryptedString
	{
		return $this->encryptedPassword;
	}

	public function setDatabase(string $database): DatabaseConnectionConfig
	{
		$this->database = $database;

		return $this;
	}

	public function getDatabase(): string
	{
		return $this->database;
	}

	public function setCharset(?string $charset): DatabaseConnectionConfig
	{
		$this->charset = $charset;

		return $this;
	}

	public function getCharset(): ?string
	{
		return $this->charset;
	}

	public function setIsProfiled(bool $value): DatabaseConnectionConfig
	{
		$this->isProfiled = $value;

		return $this;
	}

	public function getIsProfiled(): bool
	{
		return $this->isProfiled;
	}

	public function getPDODSN(): string
	{
		return "{$this->getSchema()}:dbname={$this->getDatabase()};host={$this->getHost()};charset={$this->getCharset()}";
	}
}
