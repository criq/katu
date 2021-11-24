<?php

namespace Katu\PDO;

class Process
{
	protected $id;
	protected $user;
	protected $host;
	protected $database;
	protected $command;
	protected $time;
	protected $state;
	protected $info;

	public function __construct(array $item)
	{
		$this->id = $item['Id'];
		$this->user = $item['User'];
		$this->host = $item['Host'];
		$this->database = $item['db'];
		$this->command = $item['Command'];
		$this->time = $item['Time'];
		$this->state = $item['State'];
		$this->info = $item['Info'];
	}
}
