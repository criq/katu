<?php

namespace Katu\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

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
	public function render(string $template, Request $request = null, Response $response = null, array $args = []) : Response
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

	public function renderError(Request $request = null, Response $response = null, array $args = [], $status = 500) : Response
	{
		return $this->render("Errors/" . $status . ".twig", $request, $response, $args)
			->withStatus($status)
			;
	}

	public function renderNotFound(Request $request, Response $response, array $args = [], $status = 404) : Response
	{
		return $this->renderError($request, $response, $args, $status);
	}

	public function renderUnauthorized(Request $request, Response $response, array $args = [], $status = 401) : Response
	{
		return $this->renderError($request, $response, $args, $status);
	}

	/****************************************************************************
	 * Redirect.
	 */
	public function redirect($urls, $status = 302) : Response
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
	public function isSubmitted(Request $request, string $name = null) : bool
	{
		return $request->getParam('formSubmitted') && $request->getParam('formName') == $name;
	}

	public function isSubmittedWithToken(Request $request, string $name = null) : bool
	{
		return $this->isSubmitted($request, $name) && \Katu\Tools\Security\CSRF::isValidToken($request->getParam('formToken'));
	}

	public function isSubmittedByHuman(Request $request, string $name = null) : bool
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
	public function addErrors(\Katu\Exceptions\Exception $e) : bool
	{
		if (!($this->data['_errors'] ?? null)) {
			$this->data['_errors'] = new \Katu\Exceptions\Exceptions;
		}

		$this->data['_errors']->add($e);

		return true;
	}

	public function hasErrors() : bool
	{
		return (bool)$this->data['_errors'] ?? null;
	}
}
