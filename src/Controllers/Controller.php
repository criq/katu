<?php

namespace Katu\Controllers;

class Controller
{
	protected $container;
	protected $data = [];
	protected $errors;

	public function setContainer(\Psr\Container\ContainerInterface $value): Controller
	{
		$this->request = $value;

		return $this;
	}

	public function getContainer(): \Psr\Container\ContainerInterface
	{
		return $this->request;
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
				return $this->getContainer()->get("response")->withRedirect($url, $status);
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

		return true;
	}

	/****************************************************************************
	 * View data.
	 */
	public function getViewData(): array
	{
		return array_merge($this->data, [
			"errors" => $this->getErrors(),
		]);
	}

	/****************************************************************************
	 * Errors.
	 */
	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		if (!($this->errors ?? null)) {
			$this->errors = new \Katu\Errors\ErrorCollection;
		}

		return $this->errors;
	}

	public function addError(\Katu\Errors\Error $error): Controller
	{
		$this->getErrors()->addError($error);

		return $this;
	}

	public function addErrors(\Katu\Errors\ErrorCollection $errors): Controller
	{
		$this->getErrors()->addErrors($errors);

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
