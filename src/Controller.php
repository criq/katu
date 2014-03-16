<?php

namespace Jabli;

use \Jabli\FW;

class Controller {

	static $data = array();

	static function render($view) {
		try {

			$app = FW::getApp();

			$loader = new \Twig_Loader_Filesystem('./app/Views/');
			$twig   = new \Twig_Environment($loader, array(
				'cache'       => Utils\FS::joinPaths(TMP_PATH, 'twig'),
				'auto_reload' => TRUE,
			));

			$twig->addFilter(new \Twig_SimpleFilter('url', function($string) {
				return Utils\URL::getSite($string);
			}));

			$twig->addFunction(new \Twig_SimpleFunction('getCSRFToken', function() {
				return Utils\CSRF::getFreshToken();
			}));

			$app->response->setStatus(200);
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');

			echo trim($twig->render($view . '.tpl', static::$data));

			return TRUE;

		} catch (Exception $e) {

			user_error($e);
			die('Error rendering the template.');

		}
	}

	static function addError($error) {
		return self::$data['errors'][] = $error;
	}

	static function isSubmittedWithToken($name = NULL) {
		$app = FW::getApp();

		return $app->request->params('form_submitted')
			&& $app->request->params('form_name') == $name
			&& Utils\CSRF::isValidToken($app->request->params('form_token'));
	}

}
