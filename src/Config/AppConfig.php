<?php

namespace Katu\Config;

use Katu\Types\TURL;

abstract class AppConfig extends \Katu\Config\Config
{
	public function getIsEnvironment(string $environment): bool
	{
		return mb_strtoupper($environment) === mb_strtoupper($this->getEnvironment());
	}

	public function getEnvironment(): string
	{
		return \App\App::getEnvConfig()->getVariable("APP_ENV");
	}

	public function getBaseURL(): TURL
	{
		$env = \App\App::getEnvConfig();

		return new TURL("{$env->getVariable("APP_SCHEMA")}://{$env->getVariable("APP_HOST")}/");
	}

	public function getAPIURL(): ?TURL
	{
		return $this->getBaseURL();
	}

	public function getDIDefinitions(): array
	{
		return [
			\Katu\Models\Presets\AccessToken::class => \App\Models\Users\AccessToken::class,
			\Katu\Models\Presets\EmailAddress::class => \App\Models\EmailAddress::class,
			\Katu\Models\Presets\File::class => \App\Models\File::class,
			\Katu\Models\Presets\FileAttachment::class => \App\Models\FileAttachment::class,
			\Katu\Models\Presets\Role::class => \App\Models\Users\Role::class,
			\Katu\Models\Presets\RolePermission::class => \App\Models\Users\RolePermission::class,
			\Katu\Models\Presets\Setting::class => \App\Models\Setting::class,
			\Katu\Models\Presets\User::class => \App\Models\Users\User::class,
			\Katu\Models\Presets\UserPermission::class => \App\Models\Users\UserPermission::class,
			\Katu\Models\Presets\UserRole::class => \App\Models\Users\UserRole::class,
			\Katu\Models\Presets\UserSetting::class => \App\Models\Users\UserSetting::class,
		];
	}
}
