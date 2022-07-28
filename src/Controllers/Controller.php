<?php

namespace Katu\Controllers;

use Psr\Http\Message\ServerRequestInterface;

class Controller
{
	protected $data = [];
	protected $errors;

	/****************************************************************************
	 * Form submission.
	 */
	public function isSubmitted(ServerRequestInterface $request, ?string $name = null)
	{
		return ($request->getParsedBody()["formSubmitted"] ?? null) && ($request->getParsedBody()["formName"] ?? null) == $name;
	}

	public function isSubmittedWithToken(ServerRequestInterface $request, ?string $name = null)
	{
		return $this->isSubmitted($request, $name) && \Katu\Tools\Forms\Token::validate($request->getParsedBody()["formToken"] ?? null);
	}

	public function isSubmittedByHuman(ServerRequestInterface $request, ?string $name = null)
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
