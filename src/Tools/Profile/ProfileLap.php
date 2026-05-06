<?php

namespace Katu\Tools\Profile;

use Katu\Tools\Calendar\Seconds;

class ProfileLap
{
	protected $name;
	protected $duration;

	public function __construct(string $name, Seconds $duration)
	{
		$this->setName($name);
		$this->setDuration($duration);
	}

	public function setName(string $name): ProfileLap
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setDuration(Seconds $duration): ProfileLap
	{
		$this->duration = $duration;

		return $this;
	}

	public function getDuration(): Seconds
	{
		return $this->duration;
	}
}
