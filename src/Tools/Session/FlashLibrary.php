<?php

namespace Katu\Tools\Session;

class FlashLibrary extends Library
{
	const KEY = "KATU_FLASH_LIBRARY";

	public function addFlash(Flash $flash): FlashLibrary
	{
		$this[] = $flash;

		return $this;
	}
}
