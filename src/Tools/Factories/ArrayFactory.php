<?php

namespace Katu\Tools\Factories;

class ArrayFactory extends Factory
{
	public function create(): array
	{
		return (array)func_get_arg(0);
	}
}
