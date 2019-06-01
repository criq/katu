<?php

namespace Katu\Tools\Files;

class Temporary extends File {

	public function __construct($name) {
		return parent::__construct(TMP_PATH, (string)new \Katu\Tools\Strings\Key($name));
	}

}
