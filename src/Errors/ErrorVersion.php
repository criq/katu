<?php

namespace Katu\Errors;

use Katu\Tools\Intl\Locale;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorVersion implements RestResponseInterface
{
	protected $locale;
	protected $message;

	public function __construct(Locale $locale, string $message)
	{
		$this->setLocale($locale);
		$this->setMessage($message);
	}

	public function __toString(): string
	{
		return (string)$this->getMessage();
	}

	public function setLocale(Locale $locale): ErrorVersion
	{
		$this->locale = $locale;

		return $this;
	}

	public function getLocale(): Locale
	{
		return $this->locale;
	}

	public function setMessage(?string $value): ErrorVersion
	{
		$this->message = $value;

		return $this;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"locale" => (string)$this->getLocale(),
			"message" => $this->getMessage(),
		]);
	}
}
