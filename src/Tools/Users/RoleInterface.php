<?php

namespace Katu\Tools\Users;

interface RoleInterface
{
	public function getId(): ?string;
	public function getName(): string;
	public function setName(string $name): RoleInterface;
	public function hasPermission($permission): bool;
	public function getPermissions(): array;
}
