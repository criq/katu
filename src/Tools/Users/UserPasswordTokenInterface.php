<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\UserInterface;

interface UserPasswordTokenInterface
{
	public function getId(): ?string;
	public function getUser(): UserInterface;
	public function setUser(UserInterface $user): UserPasswordTokenInterface;
	public function getToken(): string;
	public function setToken(string $token): UserPasswordTokenInterface;
	public function getIsValid(): bool;
	public function getIsExpired(): bool;
	public function expire(): UserPasswordTokenInterface;
}
