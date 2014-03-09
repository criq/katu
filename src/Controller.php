<?php

namespace Jabli;

use \Jabli\App;

class Controller {

	static function render($view, $data = array()) {
		$loader = new \Twig_Loader_Filesystem('./app/Views/');
		$twig   = new \Twig_Environment($loader, array(
			'cache'       => Utils\FS::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => TRUE,
		));

		echo trim($twig->render($view . '.tpl', $data));

		return TRUE;
	}

}
