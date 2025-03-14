<?php

namespace Katu\Config;

abstract class UserPermissionConfig extends \Katu\Config\Config
{
	abstract public function getPermissions(): array;
}
