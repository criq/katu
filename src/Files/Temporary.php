<?php

namespace Katu\Files;

class Temporary extends File
{
	const DEFAULT_DIR = 'tmp';
	const DEFAULT_PUBLIC_DIR_NAME = 'public/tmp';
	const DEFAULT_PUBLIC_URL = 'tmp';

	public function __construct()
	{
		return parent::__construct(\Katu\App::getTemporaryDir(), static::generatePath(func_get_args()));
	}
}
