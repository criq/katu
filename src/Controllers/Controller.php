<?php

namespace Katu\Controllers;

class Controller {

	public $container;
	public $data = [];

	public function __construct(\Psr\Container\ContainerInterface $container) {
		$this->container = $container;
	}

	/****************************************************************************
	 * Render.
	 */

	public function render($request, $response, $args, $template) {
		try {

			$viewClass = \Katu\App::getViewClass();
			$template = $viewClass::render($request, $response, $args, $template, $this->data);

			$headers = $request->getHeader('Accept-Encoding');
			if (isset($headers[0]) && in_array('gzip', array_map('trim', (array)explode(',', $headers[0])))) {
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

	public function renderError($request, $response, $args, $status = 500) {
		return $this->render($request, $response, $args, 'Errors/' . $status . '.twig')
			->withStatus($status)
			;
	}

	public function renderNotFound($request, $response, $args, $status = 404) {
		return $this->renderError($request, $response, $args, $status);
	}

	public function renderUnauthorized($request, $response, $args, $status = 401) {
		return $this->renderError($request, $response, $args, $status);
	}

	/****************************************************************************
	 * Redirect.
	 */

	public function redirect($urls, $status = 302) {
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

	public function isSubmitted($request, $name = null) {
		return $request->getParsedBodyParam('formSubmitted') && $request->getParsedBodyParam('formName') == $name;
	}

	public function isSubmittedWithToken($request, $name = null) {
		return $this->isSubmitted($request, $name) && \Katu\Tools\Security\CSRF::isValidToken($request->getParsedBodyParam('formToken'));
	}

	public function isSubmittedByHuman($request, $name = null) {
		// Check basic form params.
		if (!$this->isSubmittedWithToken($request, $name)) {
			return false;
		}

		// Get the token.
		$token = \Katu\Tools\Security\CSRF::getValidTokenByToken($request->getParsedBodyParam('formToken'));
		if (!$token) {
			return false;
		}

		// Check token age. Compare with tokens minDuration.
		if ($token->getAge() < $token->minDuration) {
			return false;
		}

		// Check captcha. Should be empty.
		if ($request->getParsedBodyParam('yourName_' . $token->secret) !== '') {
			return false;
		}

		return true;
	}

	public function getSubmittedFormWithToken($request, $name = null) {
		if ($this->isSubmittedWithToken($request, $name)) {
			return new \Katu\Tools\Forms\Evaluation($name);
		}

		return false;
	}

	/****************************************************************************
	 * Errors
	 */

	public function addErrors(\Katu\Exceptions\Exception $e) {
		if (!isset($this->data['_errors'])) {
			$this->data['_errors'] = new \Katu\Exceptions\ExceptionCollection;
		}

		$this->data['_errors']->add($e);

		return true;
	}

	public function hasErrors() {
		return (bool) (isset($this->data['_errors']) ? $this->data['_errors'] : false);
	}

}
