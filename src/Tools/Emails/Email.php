<?php

namespace Katu\Tools\Emails;

use Katu\Types\TEmailAddress;

abstract class Email
{
	protected $attachments;
	protected $cc = [];
	protected $headers = [];
	protected $html = "";
	protected $plain = "";
	protected $recipients = [];
	protected $replyTo;
	protected $sender;
	protected $subject;

	abstract public function send();

	public function __construct($subject = null)
	{
		$this->setSubject($subject);

		return $this;
	}

	public function __toString(): string
	{
		return (string)$this->getHTML();
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

	public function getDispatchedHTML(): ?string
	{
		return $this->getHTML();
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

	public function getDispatchedPlain(): ?string
	{
		return $this->getPlain() ?: strip_tags($this->getDispatchedHTML());
	}

	public function setBody(string $html, ?string $plain = null): Email
	{
		$this->setHTML($html);
		$this->setPlain($plain ?: strip_tags($html));

		return $this;
	}

	public function setSender(TEmailAddress $emailAddress): Email
	{
		$this->sender = $emailAddress;

		return $this;
	}

	public function getSender(): ?TEmailAddress
	{
		return $this->sender;
	}

	public function setReplyTo(?TEmailAddress $emailAddress): Email
	{
		$this->replyTo = $emailAddress;

		return $this;
	}

	public function getReplyTo(): ?TEmailAddress
	{
		return $this->replyTo;
	}

	public function addRecipient(TEmailAddress $emailAddress): Email
	{
		$this->recipients[] = $emailAddress;

		return $this;
	}

	public function resetRecipients(): Email
	{
		$this->recipients = [];

		return $this;
	}

	public function getRecipients(): array
	{
		return (array)$this->recipients;
	}

	public function addCc(TEmailAddress $emailAddress): Email
	{
		$this->cc[] = $emailAddress;

		return $this;
	}

	public function addHeader($name, $value): Email
	{
		$this->headers[$name] = $value;

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

	public function getDispatchedAttachments(): array
	{
		return array_map(function (Attachment $attachment) {
			return [
				"type" => $attachment->getEntity()->getContentType(),
				"name" => $attachment->getName() ?: $attachment->getEntity()->getFileName(),
				"content" => base64_encode($attachment->getEntity()->getContents()),
			];
		}, $this->getAttachments()->getArrayCopy());
	}
}
