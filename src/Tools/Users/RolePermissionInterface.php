<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\RoleInterface;

interface RolePermissionInterface
{
	public function getId(): ?string;
	public function getRole(): RoleInterface;
	public function getPermission(): string;
}
