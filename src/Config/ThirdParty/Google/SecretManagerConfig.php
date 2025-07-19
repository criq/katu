<?php

namespace Katu\Config\ThirdParty\Google;

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Katu\Files\File;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class SecretManagerConfig extends \Katu\Config\Config
{
	public function getServiceAccountFile(): File
	{
		return new File(\App\App::getBaseDir(), \App\App::getEnvConfig()->getVariable("SECRET_MANAGER_KEY_FILE"));
	}

	public function getProjectId(): string
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->getServiceAccountFile()->get())["project_id"];
	}

	public function getClient(): SecretManagerServiceClient
	{
		return new SecretManagerServiceClient([
			"credentials" => $this->getServiceAccountFile()->getPath(),
		]);
	}

	public function getSecret(string $name, string $version = "latest"): string
	{
		return (new \Katu\Cache\General(new TIdentifier(__CLASS__, __FUNCTION__, $name, $version), new Timeout("1 day"), function () use ($name, $version) {
			try {
				$client = $this->getClient();

				$name = $client->secretVersionName($this->getProjectId(), $name, $version);
				$response = $client->accessSecretVersion($name);
				$data = $response->getPayload()->getData();

				return $data;
			} catch (\Exception $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

				return null;
			}
		}))->getResult();
	}

	public function setSecret(string $name, string $value): string
	{
		$secretName = $this->getClient()->secretName($this->getProjectId(), $name);

		try {
			$this->getClient()->getSecret($secretName);
		} catch (\Google\ApiCore\ApiException $e) {
			if ((\Katu\Files\Formats\JSON::decodeAsArray($e->getMessage())["status"] ?? null) == "NOT_FOUND") {
				$parent = "projects/{$this->getProjectId()}";
				$secretId = $name;

				$secret = new \Google\Cloud\SecretManager\V1\Secret;
				$secret->setReplication(new \Google\Cloud\SecretManager\V1\Replication([
					"automatic" => new \Google\Cloud\SecretManager\V1\Replication\Automatic(),
				]));

				$this->getClient()->createSecret($parent, $secretId, $secret);
			} else {
				throw $e;
			}
		}

		return $this->getClient()->addSecretVersion($secretName, new SecretPayload([
			"data" => $value,
		]))->getName();
	}
}
