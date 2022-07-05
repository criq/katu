<?php

namespace Katu;

use \Katu\App;

class Controller
{
	public static $data = [];

	public static function init()
	{
		return true;
	}

	public static function redirect($urls, $code = 302)
	{
		$app = App::get();

		$urls = is_array($urls) ? $urls : [$urls];
		$urls = array_values(array_filter($urls));

		foreach ($urls as $url) {
			$url = (string) $url;
			if (\Katu\Types\TUrl::isValid($url)) {
				try {
					return $app->redirect($url, $code);
				} catch (\Slim\Exception\Stop $e) {
					return;
				}
			}
		}

		return false;
	}

	public static function prepareBody($body)
	{
		$app = App::get();

		if (in_array('gzip', array_map('trim', (array)explode(',', $app->request->headers('Accept-Encoding'))))) {
			$body = gzencode($body);
			$app->response->headers->set('Content-Encoding', 'gzip');
		}

		return $body;
	}

	public static function render($template, $code = 200, $headers = [])
	{
		$app = App::get();

		try {
			$viewClass = App::getViewClass();

			$app->response->setStatus($code);
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
			$app->response->setBody(static::prepareBody($viewClass::render($template, static::$data)));

			// Remove flash memory.
			Flash::reset();

			return true;
		} catch (\Exception $e) {
			throw new Exceptions\TemplateException($e);
		}
	}

	public static function renderError($code = 500)
	{
		return static::render('Errors/' . $code, $code);
	}

	public static function renderNotFound($code = 404)
	{
		return static::renderError($code);
	}

	public static function renderUnauthorized($code = 401)
	{
		return static::renderError($code);
	}

	public static function isSubmitted($name = null)
	{
		$app = App::get();

		return $app->request->params('formSubmitted')
			&& $app->request->params('formName') == $name
			;
	}

	public static function isSubmittedWithToken($name = null)
	{
		$app = App::get();

		return static::isSubmitted($name)
			&& Utils\CSRF::isValidToken($app->request->params('formToken'))
			;
	}

	public static function isSubmittedByHuman($name = null)
	{
		$app = App::get();

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

	public static function getSubmittedFormWithToken($name = null)
	{
		if (static::isSubmittedWithToken($name)) {
			return new Form\Evaluation($name);
		}

		return false;
	}

	public static function addError($error)
	{
		if (!isset(static::$data['_errors'])) {
			static::$data['_errors'] = new Errors;
		}

		static::$data['_errors']->addError($error);

		return true;
	}

	public static function hasErrors()
	{
		return (bool) (isset(static::$data['_errors']) ? static::$data['_errors'] : false);
	}
}
