<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\UserInterface;

interface AccessTokenInterface
{
	public function getId(): ?string;
	public function getToken(): string;
	public function setToken(string $token): AccessTokenInterface;
	public function getUser(): UserInterface;
	public function setUser(UserInterface $user): AccessTokenInterface;
	public function getIsValid(): bool;
	public function getTimeExpires(): \Katu\Tools\Calendar\Time;
}
