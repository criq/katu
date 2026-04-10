<?php

namespace Katu\Tools\Profile;

use Katu\Tools\Calendar\Seconds;

class ProfileLapCollection extends \ArrayObject
{
	public function addLap(ProfileLap $lap): ProfileLapCollection
	{
		$this->append($lap);

		return $this;
	}

	/**
	 * @return array<string, Seconds>
	 */
	public function getTotalsByName(): array
	{
		$totals = [];
		foreach ($this as $lap) {
			if (!$lap instanceof ProfileLap) {
				continue;
			}
			$name = $lap->getName();
			$value = $lap->getDuration()->getValue();
			if (!isset($totals[$name])) {
				$totals[$name] = 0.0;
			}
			$totals[$name] += $value;
		}

		$res = [];
		foreach ($totals as $name => $value) {
			$res[$name] = new Seconds($value);
		}

		return $res;
	}
}
