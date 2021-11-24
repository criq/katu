<?php

namespace Katu\PDO;

class Process
{
	public $id;
	public $user;
	public $host;
	public $database;
	public $command;
	public $time;
	public $state;
	public $info;

	public function __construct(array $item)
	{
		$this->id = (int)$item['Id'];
		$this->user = $item['User'];
		$this->host = $item['Host'];
		$this->database = $item['db'];
		$this->command = $item['Command'];
		$this->time = (int)$item['Time'];
		$this->state = $item['State'];
		$this->info = $item['Info'];
	}
}
