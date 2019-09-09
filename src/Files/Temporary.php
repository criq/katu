<?php

namespace Katu\Files;

class Temporary extends File {

	public function __construct() {
		return parent::__construct(TMP_PATH, (string)(new \Katu\Tools\Keys\Types\TArray(func_get_args())));
	}

}
