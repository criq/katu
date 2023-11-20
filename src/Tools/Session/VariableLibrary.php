<?php

namespace Katu\Tools\Session;

class VariableLibrary extends Library
{
	const KEY = "KATU_VARIABLE_LIBRARY";

	public function setVariable(string $key, $value): Library
	{
		$this[$key] = $value;

		return $this;
	}

	public function getVariable(string $key)
	{
		return $this[$key] ?? null;
	}

	public function unsetVariable(string $key): Library
	{
		if (isset($this[$key])) {
			unset($this[$key]);
		}

		return $this;
	}
}
