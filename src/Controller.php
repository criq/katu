<?php

namespace Jabli;

use \Jabli\App;

class Controller {

	static function render($view, $data = array()) {
		return App::getApp()->render($view . '.php', $data);
	}

}
