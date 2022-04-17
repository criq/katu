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
	public function getViewEngine(?\Slim\Http\Request $request = null): \Katu\Tools\Views\TwigEngine
	{
		return new \Katu\Tools\Views\FilesystemLoaderTwigEngine($request);
	}

	public function render(string $template)
	{
		try {
			$request = $this->container->get("request");
			$response =  $this->container->get("response");

			$engine = $this->getViewEngine($request);
			$template = $engine->render($template, $this->data);

			$headers = $request->getHeader("Accept-Encoding");
			if (($headers[0] ?? null) && in_array("gzip", array_map("trim", (array)explode(",", $headers[0])))) {
				$template = gzencode($template);
				$response = $response->withHeader("Content-Encoding", "gzip");
			}

			$response->write($template);

			// Reset flash memory.
			\Katu\Tools\Session\Flash::reset();

			return $response;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\TemplateException($e);
		}
	}

	public function renderError(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args = [], $status = 500)
	{
		return $this->render("Errors/{$status}.twig", $request, $response, $args)
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
				return $this->container->get("response")->withRedirect($url, $status);
			}
		}

		return false;
	}

	/****************************************************************************
	 * Form submission.
	 */
	public function isSubmitted(\Slim\Http\Request $request, ?string $name = null)
	{
		return $request->getParam("formSubmitted") && $request->getParam("formName") == $name;
	}

	public function isSubmittedWithToken(\Slim\Http\Request $request, ?string $name = null)
	{
		return $this->isSubmitted($request, $name) && \Katu\Tools\Forms\Token::validate($request->getParam("formToken"));
	}

	public function isSubmittedByHuman(\Slim\Http\Request $request, ?string $name = null)
	{
		// Check basic form params.
		if (!$this->isSubmittedWithToken($request, $name)) {
			return false;
		}

		// // Check captcha. Should be empty.
		// if ($request->getParam("yourName_" . $request->getParam("")->secret) !== "") {
		// 	return false;
		// }

		return true;
	}

	/****************************************************************************
	 * Errors.
	 */
	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		if (!($this->data["_errors"] ?? null)) {
			$this->data["_errors"] = new \Katu\Errors\ErrorCollection;
		}

		return $this->data["_errors"];
	}

	public function addError(\Katu\Errors\Error $error): Controller
	{
		$this->getErrors()->addError($error);

		return $this;
	}

	public function addErrors(\Katu\Errors\ErrorCollection $errorCollection): Controller
	{
		$this->getErrors()->addErrorCollection($errorCollection);

		return $this;
	}

	public function hasErrors(): bool
	{
		return (bool)count($this->getErrors());
	}

	public function getExceptions(): \Katu\Exceptions\ExceptionCollection
	{
		if (!($this->data["_exceptions"] ?? null)) {
			$this->data["_exceptions"] = new \Katu\Exceptions\ExceptionCollection;
		}

		return $this->data["_exceptions"];
	}

	public function addExceptions(\Katu\Exceptions\Exception $e): Controller
	{
		$this->getExceptions()->add($e);

		return $this;
	}

	public function hasExceptions(): bool
	{
		return (bool)count($this->getExceptions());
	}
}
