<?php

namespace Katu\Files;

class Temporary extends File {

	public function __construct() {
		return parent::__construct(\Katu\App::getTmpDir(), (string)(new \Katu\Tools\Keys\Types\TArray(func_get_args())));
	}

}
