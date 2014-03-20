<?php

namespace Jabli;

use \Jabli\FW;

class Controller {

	static $data = array();

	static function render($template, $code = 200) {
		$app = FW::getApp();

		try {

			$app->response->setStatus($code);
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');

			echo View::render($template, static::$data);

			return TRUE;

		} catch (Exception $e) {

			user_error($e);
			die('Error rendering the template.');

		}
	}

	static function renderError($code = 500) {
		return self::render('Errors/' . $code, $code);
	}

	static function renderNotFound($code = 404) {
		return self::renderError($code);
	}

	static function renderUnauthorized($code = 401) {
		return self::renderError($code);
	}

	static function addError($error) {
		return self::$data['_errors'][] = $error;
	}

	static function isSubmittedWithToken($name = NULL) {
		$app = FW::getApp();

		return $app->request->params('form_submitted')
			&& $app->request->params('form_name') == $name
			&& Utils\CSRF::isValidToken($app->request->params('form_token'));
	}

}
