<?php

namespace Katu\Tools\Emails;

use Katu\Tools\HTTPTools\Header;
use Katu\Tools\HTTPTools\HeaderCollection;
use Katu\Types\TEmailAddress;
use Katu\Types\TEmailAddressCollection;

class Email
{
	protected $attachments;
	protected $ccs;
	protected $dispatchable;
	protected $headers;
	protected $html;
	protected $isDispatchable;
	protected $plain;
	protected $providerConfigurations;
	protected $recipients;
	protected $recipientVariables;
	protected $replyTo;
	protected $sender;
	protected $subject;
	protected $template;
	protected $variables;

	public function __toString(): string
	{
		return (string)$this->getHTML() ?: (string)$this->getPlain();
	}

	public function setHeaders(?HeaderCollection $headers): Email
	{
		$this->headers = $headers;

		return $this;
	}

	public function getHeaders(): HeaderCollection
	{
		if (is_null($this->headers)) {
			$this->headers = new HeaderCollection;
		}

		return $this->headers;
	}

	public function addHeader(Header $header): Email
	{
		$this->getHeaders()[] = $header;

		return $this;
	}

	public function setSender(?TEmailAddress $emailAddress): Email
	{
		$this->sender = $emailAddress;

		return $this;
	}

	public function getSender(): ?TEmailAddress
	{
		return $this->sender;
	}

	public function setRecipients(?TEmailAddressCollection $recipients): Email
	{
		$this->recipients = $recipients;

		return $this;
	}

	public function getRecipients(): TEmailAddressCollection
	{
		if (is_null($this->recipients)) {
			$this->recipients = new TEmailAddressCollection;
		}

		return $this->recipients;
	}

	public function addRecipient(TEmailAddress $emailAddress): Email
	{
		$this->getRecipients()[] = $emailAddress;

		return $this;
	}

	public function setCCs(?TEmailAddressCollection $ccs): Email
	{
		$this->ccs = $ccs;

		return $this;
	}

	public function getCCs(): TEmailAddressCollection
	{
		if (is_null($this->ccs)) {
			$this->ccs = new TEmailAddressCollection;
		}

		return $this->ccs;
	}

	public function addCC(TEmailAddress $emailAddress): Email
	{
		$this->getCCs()[] = $emailAddress;

		return $this;
	}

	public function setReplyTo(?TEmailAddress $replyTo): Email
	{
		$this->replyTo = $replyTo;

		return $this;
	}

	public function getReplyTo(): ?TEmailAddress
	{
		return $this->replyTo;
	}

	public function setSubject(?string $subject): Email
	{
		$this->subject = $subject;

		return $this;
	}

	public function getSubject(): ?string
	{
		return $this->subject;
	}

	public function setHTML(?string $html): Email
	{
		$this->html = $html;

		return $this;
	}

	public function getHTML(): ?string
	{
		return $this->html;
	}

	public function getResolvedHTML(): ?string
	{
		return $this->getHTML() ?: nl2br($this->getPlain());
	}

	public function setPlain(?string $plain): Email
	{
		$this->plain = $plain;

		return $this;
	}

	public function getPlain(): ?string
	{
		return $this->plain;
	}

	public function getResolvedPlain(): ?string
	{
		return $this->getPlain() ?: strip_tags($this->getHTML());
	}

	public function setAttachments(?AttachmentCollection $attachments): Email
	{
		$this->attachments = $attachments;

		return $this;
	}

	public function getAttachments(): AttachmentCollection
	{
		if (is_null($this->attachments)) {
			$this->attachments = new AttachmentCollection;
		}

		return $this->attachments;
	}

	public function addAttachments(AttachmentCollection $attachments): Email
	{
		$this->getAttachments()->addAttachments($attachments);

		return $this;
	}

	public function addAttachment(Attachment $attachment): Email
	{
		$this->getAttachments()->addAttachment($attachment);

		return $this;
	}

	public function setTemplate(?string $template): Email
	{
		$this->template = $template;

		return $this;
	}

	public function getTemplate(): ?string
	{
		return $this->template;
	}

	public function setVariables(?VariableCollection $variables): Email
	{
		$this->variables = $variables;

		return $this;
	}

	public function getVariables(): VariableCollection
	{
		if (is_null($this->variables)) {
			$this->variables = new VariableCollection;
		}

		return $this->variables;
	}

	public function addVariable(Variable $variable): Email
	{
		$this->getVariables()[] = $variable;

		return $this;
	}

	public function setRecipientVariables(?RecipientVariableCollection $recipientVariables): Email
	{
		$this->recipientVariables = $recipientVariables;

		return $this;
	}

	public function getRecipientVariables(): RecipientVariableCollection
	{
		if (is_null($this->recipientVariables)) {
			$this->recipientVariables = new RecipientVariableCollection;
		}

		return $this->recipientVariables;
	}

	public function addRecipientVariable(RecipientVariable $recipientVariable): Email
	{
		$this->getRecipientVariables()[] = $recipientVariable;

		return $this;
	}

	public function getProviderConfigurations(): ProviderConfigurationCollection
	{
		if (is_null($this->providerConfigurations)) {
			$this->providerConfigurations = new ProviderConfigurationCollection;
		}

		return $this->providerConfigurations;
	}

	public function addProviderConfiguration(ProviderConfiguration $providerConfiguration): Email
	{
		$this->getProviderConfigurations()[] = $providerConfiguration;

		return $this;
	}

	protected function setIsDispatchable(bool $isDispatchable): Email
	{
		$this->isDispatchable = $isDispatchable;

		return $this;
	}

	public function getIsDispatchable(): bool
	{
		return $this->isDispatchable;
	}

	public function getDispatchable(): Email
	{
		if (is_null($this->dispatchable)) {
			$this->dispatchable = $this->createDispatchable();
		}

		$this->dispatchable->setIsDispatchable(true);

		return $this->dispatchable;
	}

	protected function createDispatchable(): Email
	{
		return clone $this;
	}

	public function dispatch(Provider $provider): ?Response
	{
		return $provider->createRequest($this->getDispatchable())->createResponse();
	}
}
