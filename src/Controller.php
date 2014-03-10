<?php

namespace Jabli;

use \Jabli\App;

class Controller {

	static function render($view, $data = array()) {
		$app = App::getApp();

		$loader = new \Twig_Loader_Filesystem('./app/Views/');
		$twig   = new \Twig_Environment($loader, array(
			'cache'       => Utils\FS::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => TRUE,
		));

		$twig->addFilter(new \Twig_SimpleFilter('url', function ($string) {
			return Utils\URL::getSite($string);
		}));

		$app->response->setStatus(200);
		$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');

		echo trim($twig->render($view . '.tpl', $data));

		return TRUE;
	}

}
