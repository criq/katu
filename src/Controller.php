<?php

namespace Katu;

use \Katu\App;

class Controller {

	static $errors = array();
	static $data   = array();

	static function redirect($url = NULL, $code = 302) {
		try {
			App::get()->redirect($url, $code);
		} catch (\Exception $e) {

		}
	}

	static function render($template, $code = 200, $headers = array()) {
		$app = App::get();

		try {

			static::$data['_errors']  = static::$errors;

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
		return static::render('Errors/' . $code, $code);
	}

	static function renderNotFound($code = 404) {
		return static::renderError($code);
	}

	static function renderUnauthorized($code = 401) {
		return static::renderError($code);
	}

	static function isSubmittedWithToken($name = NULL) {
		$app = App::get();

		return $app->request->params('formSubmitted')
			&& $app->request->params('formName') == $name
			&& Utils\CSRF::isValidToken($app->request->params('formToken'));
	}

	static function isSubmittedByHuman($name = NULL) {
		$app = App::get();

		// Check basic form params.
		if (!static::isSubmittedWithToken($name)) {
			return FALSE;
		}

		// Get the token.
		$token = Utils\CSRF::getValidTokenByToken($app->request->params('formToken'));
		if (!$token) {
			return FALSE;
		}

		// Check token age. Should be more than 1 second.
		if ($token->getAge() < 1) {
			return FALSE;
		}

		// Check captcha. Should be empty.
		if ($app->request->params('cValue' . $token->secret) !== '') {
			return FALSE;
		}

		return TRUE;
	}

	static function getSubmittedFormWithToken($name = NULL) {
		$app = App::get();

		if (static::isSubmittedWithToken($name)) {
			return new Form\Evaluation($name);
		}

		return FALSE;
	}

	static function addError($error) {
		if ($error instanceof \Exception) {
			return static::$errors[] = $error->getMessage();
		}

		return static::$errors[] = $error;
	}

}
