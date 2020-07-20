<?php

namespace Katu\Cache;

class Ledger
{
	public $file;

	public function __construct(\Katu\Files\File $file)
	{
		$this->file = $file;
		$this->file->touch();

		if (!$this->get()) {
			$this->set([]);
		}
	}

	public function populateKeys(array $keys)
	{
		$contents = $this->get();

		foreach ($keys as $key) {
			if (!isset($contents[$key])) {
				$contents[$key] = null;
			}
		}

		$this->set($contents);

		return true;
	}

	public function get()
	{
		return (array)\Katu\Files\Formats\JSON::decodeAsArray($this->file->get());
	}

	public function set($contents)
	{
		ksort($contents, \SORT_NATURAL);

		return $this->file->set(\Katu\Files\Formats\JSON::encode($contents));
	}

	public function setKey($key, $value)
	{
		$contents = $this->get();
		$contents[$key] = $value;
		$this->set($contents);

		return $this;
	}

	public function setKeyLoaded($key)
	{
		$this->setKey($key, array_merge((array)$this->getKey($key), [
			'timeLoaded' => (new \Katu\Tools\DateTime\DateTime)->format('r'),
		]));
	}

	public function getKey($key)
	{
		$contents = $this->get();

		if (isset($contents[$key])) {
			return $contents[$key];
		}

		return null;
	}

	public function getExpiredKeys($timeout, $timeKey = 'timeLoaded')
	{
		$cutoffDateTime = new \Katu\Tools\DateTime\DateTime(is_int($timeout) ? '-' . $timeout . 'seconds' : '- ' . $timeout);

		$expired = [];
		foreach ($this->get() as $key => $value) {
			if (isset($value[$timeKey]) && (new \Katu\Tools\DateTime\DateTime($value[$timeKey]))->isInTimeout($cutoffDateTime->getAge())) {
				// Not expired.
			} elseif (isset($value[$timeKey])) {
				$expired[$key] = 'B' . (new \Katu\Tools\DateTime\DateTime($value[$timeKey]))->getTimestamp();
			} else {
				$expired[$key] = 'A' . $key;
			}
		}

		natsort($expired);
		// var_dump($expired);die;

		return array_keys($expired);
	}
}
