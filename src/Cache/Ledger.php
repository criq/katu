<?php

namespace Katu\Cache;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class Ledger
{
	const DIR_NAME = 'ledgers';

	protected $identifier;

	public function __construct(TIdentifier $identifier)
	{
		$this->setIdentifier($identifier);
		$this->getFile()->touch();

		if (!$this->get()) {
			$this->set([]);
		}
	}

	public function setIdentifier(TIdentifier $identifier) : Ledger
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier() : TIdentifier
	{
		return $this->identifier;
	}

	public function getFile() : \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifier()->getPath('json'));
	}

	public function populateKeys(array $keys) : Ledger
	{
		$contents = $this->get();

		foreach ($keys as $key) {
			if (!isset($contents[$key])) {
				$contents[$key] = null;
			}
		}

		$this->set($contents);

		return $this;
	}

	public function get() : array
	{
		return (array)\Katu\Files\Formats\JSON::decodeAsArray($this->getFile()->get());
	}

	public function set($contents) : Ledger
	{
		ksort($contents, \SORT_NATURAL);

		$this->getFile()->set(\Katu\Files\Formats\JSON::encode($contents));

		return $this;
	}

	public function setKey($key, $value) : Ledger
	{
		$contents = $this->get();
		$contents[$key] = $value;
		$this->set($contents);

		return $this;
	}

	public function setKeyLoaded($key) : Ledger
	{
		$this->setKey($key, array_merge((array)$this->getKey($key), [
			'timeLoaded' => (new \Katu\Tools\DateTime\DateTime)->format('r'),
		]));

		return $this;
	}

	public function getKey($key)
	{
		$contents = $this->get();
		if (isset($contents[$key])) {
			return $contents[$key];
		}

		return null;
	}

	public function getExpiredKeys(Timeout $timeout, $timeKey = 'timeLoaded') : array
	{
		$expired = [];
		foreach ($this->get() as $key => $value) {
			if (isset($value[$timeKey]) && (new \Katu\Tools\DateTime\DateTime($value[$timeKey]))->fitsInTimeout($timeout)) {
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

	public function removeKey($key) : Ledger
	{
		$contents = $this->get();
		unset($contents[$key]);
		$this->set($contents);

		return $this;
	}

	public function count() : int
	{
		return count($this->get());
	}
}
