<?php

namespace Katu\PDO;

class Process
{
	public $connection;
	public $id;
	public $user;
	public $host;
	public $database;
	public $command;
	public $time;
	public $state;
	public $info;

	public function __construct(Connection $connection, array $item)
	{
		$this->connection = $connection;
		$this->id = (int)$item['Id'];
		$this->user = $item['User'];
		$this->host = $item['Host'];
		$this->database = $item['db'];
		$this->command = $item['Command'];
		$this->time = (int)$item['Time'];
		$this->state = $item['State'];
		$this->info = $item['Info'];
	}

	public function kill(): Result
	{
		$sql = " KILL {$this->id} ";

		return $this->connection->createQuery($sql)->getResult();
	}
}
