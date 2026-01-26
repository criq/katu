<?php

namespace Katu\Tools\Emails;

class Request
{
	protected $service;
	protected $email;

	public function __construct(TransactionalEmailServiceInterface $service, Email $email)
	{
		$this->setService($service);
		$this->setEmail($email);
	}

	public function setService(TransactionalEmailServiceInterface $service): Request
	{
		$this->service = $service;

		return $this;
	}

	public function getService(): TransactionalEmailServiceInterface
	{
		return $this->service;
	}

	public function setEmail(Email $email): Request
	{
		if (!$email->getIsDispatchable()) {
			throw new \Exception("Email is not dispatchable.");
		}

		$this->email = $email;

		return $this;
	}

	public function getEmail(): Email
	{
		return $this->email;
	}

	public function createResponse(): Response
	{
		return $this->getService()->dispatch($this->getEmail());
	}
}
