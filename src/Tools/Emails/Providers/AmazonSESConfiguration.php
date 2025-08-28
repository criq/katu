<?php

namespace Katu\Tools\Emails\Providers;

use Katu\Tools\Emails\ProviderConfiguration;

class AmazonSESConfiguration extends ProviderConfiguration
{
	protected $accessKeyId;
	protected $secretAccessKey;
	protected $region;
	protected $configurationSetName;

	public function setAccessKeyId(string $accessKeyId): AmazonSESConfiguration
	{
		$this->accessKeyId = $accessKeyId;

		return $this;
	}

	public function getAccessKeyId(): string
	{
		return $this->accessKeyId;
	}

	public function setSecretAccessKey(string $secretAccessKey): AmazonSESConfiguration
	{
		$this->secretAccessKey = $secretAccessKey;

		return $this;
	}

	public function getSecretAccessKey(): string
	{
		return $this->secretAccessKey;
	}

	public function setRegion(string $region): AmazonSESConfiguration
	{
		$this->region = $region;

		return $this;
	}

	public function getRegion(): string
	{
		return $this->region;
	}

	public function setConfigurationSetName(?string $configurationSetName): AmazonSESConfiguration
	{
		$this->configurationSetName = $configurationSetName;

		return $this;
	}

	public function getConfigurationSetName(): ?string
	{
		return $this->configurationSetName;
	}
}
