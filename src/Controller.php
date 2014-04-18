<?php

namespace Katu;

use \Katu\App;

class Controller {

	static $errors = array();
	static $data   = array();

	static function redirect($url = NULL, $code = 301) {
		try {
			App::get()->redirect($url, $code);
		} catch (\Exception $e) {

		}
	}

	static function render($template, $code = 200, $headers = array()) {
		$app = App::get();

		try {

			self::$data['_errors']  = self::$errors;

			$app->response->setStatus($code);
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
			$app->response->setBody(View::render($template, static::$data));

			// Remove flash memory.
			Flash::reset();

			return TRUE;

		} catch (\Exception $e) {

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

	static function isSubmittedWithToken($name = NULL) {
		$app = App::get();

		return $app->request->params('form_submitted')
			&& $app->request->params('form_name') == $name
			&& Utils\CSRF::isValidToken($app->request->params('form_token'));
	}

	static function addError($error) {
		if ($error instanceof \Exception) {
			return self::$errors[] = $error->getMessage();
		}

		return self::$errors[] = $error;
	}

}
