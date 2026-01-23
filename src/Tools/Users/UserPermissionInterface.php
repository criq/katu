<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\UserInterface;

interface UserPermissionInterface
{
	public function getId(): ?string;
	public function getUser(): UserInterface;
	public function getPermission(): string;
}
