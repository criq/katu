<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\UserInterface;

interface UserLoginTokenInterface
{
	public function getId(): ?string;
	public function getUser(): UserInterface;
	public function isValid(): bool;
	public function expire(): bool;
}
