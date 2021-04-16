<?php

namespace Katu\Controllers;

class Controller
{
	public $container;
	public $data = [];

	public function __construct(\Psr\Container\ContainerInterface $container)
	{
		$this->container = $container;
	}

	/****************************************************************************
	 * Render.
	 */
	public function render(string $template, \Slim\Http\Request $request = null, \Slim\Http\Response $response = null, array $args = [])
	{
		try {
			$request = $request ?: $this->container->get('request');
			$response = $response ?: $this->container->get('response');
			$args = $args ?: [];

			$viewClass = \Katu\App::getViewClass()->getName();
			$template = $viewClass::render($template, $this->data, $request, $response, $args);

			$headers = $request->getHeader('Accept-Encoding');
			if (($headers[0] ?? null) && in_array('gzip', array_map('trim', (array)explode(',', $headers[0])))) {
				$template = gzencode($template);
				$response = $response->withHeader('Content-Encoding', 'gzip');
			}

			$response->write($template);

			// Reset flash memory.
			\Katu\Tools\Session\Flash::reset();

			return $response;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\TemplateException($e);
		}
	}

	public function renderError(\Slim\Http\Request $request = null, \Slim\Http\Response $response = null, array $args = [], $status = 500)
	{
		return $this->render("Errors/" . $status . ".twig", $request, $response, $args)
			->withStatus($status)
			;
	}

	public function renderNotFound(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args = [], $status = 404)
	{
		return $this->renderError($request, $response, $args, $status);
	}

	public function renderUnauthorized(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args = [], $status = 401)
	{
		return $this->renderError($request, $response, $args, $status);
	}

	/****************************************************************************
	 * Redirect.
	 */
	public function redirect($urls, $status = 302)
	{
		$urls = is_array($urls) ? $urls : [$urls];
		$urls = array_values(array_filter($urls));

		foreach ($urls as $url) {
			$url = (string) $url;
			if (\Katu\Types\TURL::isValid($url)) {
				return $this->container->get('response')->withRedirect($url, $status);
			}
		}

		return false;
	}

	/****************************************************************************
	 * Form submission.
	 */
	public function isSubmitted(\Slim\Http\Request $request, string $name = null)
	{
		return $request->getParam('formSubmitted') && $request->getParam('formName') == $name;
	}

	public function isSubmittedWithToken(\Slim\Http\Request $request, string $name = null)
	{
		return $this->isSubmitted($request, $name) && \Katu\Tools\Security\CSRF::isValidToken($request->getParam('formToken'));
	}

	public function isSubmittedByHuman(\Slim\Http\Request $request, string $name = null)
	{
		// Check basic form params.
		if (!$this->isSubmittedWithToken($request, $name)) {
			return false;
		}

		// Get the token.
		$token = \Katu\Tools\Security\CSRF::getValidTokenByToken($request->getParam('formToken'));
		if (!$token) {
			return false;
		}

		// Check token age. Compare with tokens minDuration.
		if (abs($token->getAge()->getValue()) < $token->minDuration) {
			return false;
		}

		// Check captcha. Should be empty.
		if ($request->getParam('yourName_' . $token->secret) !== '') {
			return false;
		}
		
		return true;
	}

	/****************************************************************************
	 * Errors.
	 */
	public function addErrors(\Katu\Exceptions\Exception $e)
	{
		if (!($this->data['_errors'] ?? null)) {
			$this->data['_errors'] = new \Katu\Exceptions\Exceptions;
		}

		$this->data['_errors']->add($e);

		return true;
	}

	public function hasErrors()
	{
		return (bool)$this->data['_errors'] ?? null;
	}
}
