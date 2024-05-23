<?php

namespace Katu\PDO;

class Process
{
	protected $command;
	protected $connection;
	protected $database;
	protected $host;
	protected $id;
	protected $info;
	protected $state;
	protected $time;
	protected $user;

	public function __construct(Connection $connection, array $item)
	{
		$this->command = $item["Command"];
		$this->connection = $connection;
		$this->database = $item["db"];
		$this->host = $item["Host"];
		$this->id = (int)$item["Id"];
		$this->info = $item["Info"];
		$this->state = $item["State"];
		$this->time = (int)$item["Time"];
		$this->user = $item["User"];
	}

	public function getCommand(): ?string
	{
		return $this->command;
	}

	public function getInfo(): ?string
	{
		return $this->info;
	}

	public function kill(): Result
	{
		$sql = " KILL {$this->id} ";

		return $this->connection->createQuery($sql)->getResult();
	}
}
