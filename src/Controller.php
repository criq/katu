<?php

namespace Jabli;

use \Jabli\App;

class Controller {

	static function render($view, $data = array()) {
		$loader = new \Twig_Loader_Filesystem('./app/Views/');
		$twig   = new \Twig_Environment($loader, array(
			'cache'       => TMP_PATH,
			'auto_reload' => TRUE,
		));

		echo $twig->render($view . '.tpl', $data);

		return TRUE;
	}

}
