<?php

namespace Katu\Tools\Users;

use Katu\Tools\Users\UserInterface;

interface UserSettingInterface
{
	public function getId(): ?string;
	public function getUser(): UserInterface;
	public function getName(): string;
	public function getValue();
}
