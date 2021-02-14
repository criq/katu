<?php

namespace Katu\Classes\Factories;

class ArrayFactory extends Factory
{
	public function create()
	{
		return (array)func_get_arg(0);
	}
}
