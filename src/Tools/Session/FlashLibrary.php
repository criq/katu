<?php

namespace Katu\Tools\Session;

use Katu\Tools\Strings\Code;

class FlashLibrary extends Library
{
	const KEY = "KATU_FLASH_LIBRARY";

	public function addFlash(Flash $flash): FlashLibrary
	{
		$this[] = $flash;

		return $this;
	}

	public function filterByClassCode(string $code): FlashLibrary
	{
		$library = new static($this->getKey());

		$flashes = array_values(array_filter($this->getArrayCopy(), function (Flash $flash) use ($code) {
			return $flash->getClassCode()->getConstantFormat() == (new Code($code))->getConstantFormat();
		}));
		foreach ($flashes as $flash) {
			$library[] = $flash;
		}

		return $library;
	}
}
