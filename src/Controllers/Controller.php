<?php

namespace Katu\Controllers;

class Controller {

	public $data = [];

	public function render($request, $response, $args, $template) {
		try {

			$viewClass = \Katu\App::getViewClass();
			$template = $viewClass::render($request, $response, $args, $template, $this->data);

			$headers = $request->getHeader('Accept-Encoding');
			if (isset($headers[0]) && in_array('gzip', array_map('trim', (array)explode(',', $headers[0])))) {
				$template = gzencode($template);
				$response = $response->withHeader('Content-Encoding', 'gzip');
			}

			$response->getBody()->write($template);

			// Reset flash memory.
			\Katu\Tools\Session\Flash::reset();

			return $response;

		} catch (\Exception $e) {
			throw new \Katu\Exceptions\TemplateException($e);
		}
	}










	static function redirect($urls, $code = 302) {
		$app = \Katu\App::get();

		$urls = is_array($urls) ? $urls : [$urls];
		$urls = array_values(array_filter($urls));

		foreach ($urls as $url) {
			$url = (string) $url;
			if (\Katu\Types\TURL::isValid($url)) {
				try {
					return $app->redirect($url, $code);
				} catch (\Slim\Exception\Stop $e) {
					return;
				}
			}
		}

		return false;
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

	static function isSubmitted($name = null) {
		$app = \Katu\App::get();

		return $app->request->params('formSubmitted')
			&& $app->request->params('formName') == $name
			;
	}

	static function isSubmittedWithToken($name = null) {
		$app = \Katu\App::get();

		return static::isSubmitted($name)
			&& Utils\CSRF::isValidToken($app->request->params('formToken'))
			;
	}

	static function isSubmittedByHuman($name = null) {
		$app = \Katu\App::get();

		// Check basic form params.
		if (!static::isSubmittedWithToken($name)) {
			return false;
		}

		// Get the token.
		$token = Utils\CSRF::getValidTokenByToken($app->request->params('formToken'));
		if (!$token) {
			return false;
		}

		// Check token age. Compare with tokens minDuration.
		if ($token->getAge() < $token->minDuration) {
			return false;
		}

		// Check captcha. Should be empty.
		if ($app->request->params('yourName_' . $token->secret) !== '') {
			return false;
		}

		return true;
	}

	static function getSubmittedFormWithToken($name = null) {
		$app = \Katu\App::get();

		if (static::isSubmittedWithToken($name)) {
			return new Form\Evaluation($name);
		}

		return false;
	}

	static function addError($error) {
		if (!isset(static::$data['_errors'])) {
			static::$data['_errors'] = new Errors;
		}

		static::$data['_errors']->addError($error);

		return true;
	}

	static function hasErrors() {
		return (bool) (isset(static::$data['_errors']) ? static::$data['_errors'] : false);
	}

}
