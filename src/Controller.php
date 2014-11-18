<?php

namespace Katu;

use \Katu\App;

class Controller {

	static $data = array();

	static function redirect($url, $code = 302) {
		try {

			$app = App::get();

			return $app->redirect((string) $url, $code); die;

		} catch (\Exception $e) {

		}
	}

	static function render($template, $code = 200, $headers = array()) {
		$app = App::get();

		try {

			$app->response->setStatus($code);
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
			$app->response->setBody(View::render($template, static::$data));

			// Remove flash memory.
			Flash::reset();

			return TRUE;

		} catch (\Exception $e) {

			error_log($e);

			throw new Exceptions\TemplateException($e->getMessage());

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

		// Check token age. Compare with tokens minDuration.
		if ($token->getAge() < $token->minDuration) {
			return FALSE;
		}

		// Check captcha. Should be empty.
		if ($app->request->params('yourName_' . $token->secret) !== '') {
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
		if ($error instanceof \Katu\Exceptions\ArgumentErrorException) {
			static::$data['_namedArgumentsInError'][] = $error->getName();
			static::$data['_errors'][$error->getName()][] = $error->getMessage();
		} elseif ($error instanceof \Exception) {
			static::$data['_errors'][] = $error->getMessage();
		} else {
			static::$data['_errors'][] = $error;
		}

		return true;
	}

	static function hasErrors() {
		return (bool) (isset(static::$data['_errors']) ? static::$data['_errors'] : false);
	}

}
