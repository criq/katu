<?php

namespace Katu\Models\Presets;

interface EmailAddressInterface
{
	public function getId(): ?string;
	public function getEmailAddress(): string;
	public function setEmailAddress(string $emailAddress): EmailAddressInterface;
	public function getTitle(): string;
}
