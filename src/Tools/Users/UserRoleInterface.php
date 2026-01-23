<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\RoleInterface;
use Katu\Tools\Users\UserInterface;

interface UserRoleInterface
{
	public function getId(): ?string;
	public function getUser(): UserInterface;
	public function getRole(): RoleInterface;
}
