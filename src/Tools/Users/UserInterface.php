<?php

namespace Katu\Tools\Users;

use Katu\Models\Presets\EmailAddressInterface;

interface UserInterface
{
	public function getId(): ?string;
	public function getName(): ?string;
	public function setName(string $name): UserInterface;
	public function getEmailAddress(): ?EmailAddressInterface;
	public function setEmailAddress(?EmailAddressInterface $emailAddress): UserInterface;
	public function getPassword(): ?string;
	public function setPassword(?string $password): UserInterface;
	public function hasPermission(string $permission): bool;
	public function getOrCreateSafeAccessToken(): AccessTokenInterface;
}
