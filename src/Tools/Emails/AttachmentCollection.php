<?php

namespace Katu\Tools\Emails;

class AttachmentCollection extends \ArrayObject
{
	public function addAttachment(Attachment $attachment): AttachmentCollection
	{
		$this[] = $attachment;

		return $this;
	}

	public function addAttachments(AttachmentCollection $attachments): AttachmentCollection
	{
		foreach ($attachments as $attachment) {
			$this->addAttachment($attachment);
		}

		return $this;
	}
}
