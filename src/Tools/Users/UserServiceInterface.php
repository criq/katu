<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\UserInterface;

interface UserServiceInterface
{
	public function getId(): ?string;
	public function getUser(): UserInterface;
	public function getServiceName(): string;
	public function getServiceUserId(): string;
}
