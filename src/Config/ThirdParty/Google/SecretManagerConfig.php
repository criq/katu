<?php

namespace Katu\Config\ThirdParty\Google;

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Katu\Files\File;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class SecretManagerConfig extends \Katu\Config\Config
{
	protected $serviceAccountFile;
	protected $projectId;
	protected $client;

	public function getServiceAccountFile(): File
	{
		if (!$this->serviceAccountFile) {
			$this->serviceAccountFile = new File(\App\App::getBaseDir(), \App\App::getEnvConfig()->getVariable("SECRET_MANAGER_KEY_FILE"));
		}

		return $this->serviceAccountFile;
	}

	public function getProjectId(): string
	{
		if (!$this->projectId) {
			$this->projectId = \Katu\Files\Formats\JSON::decodeAsArray($this->getServiceAccountFile()->get())["project_id"];
		}

		return $this->projectId;
	}

	public function getClient(): SecretManagerServiceClient
	{
		if (!$this->client) {
			$this->client = new SecretManagerServiceClient([
				"credentials" => $this->getServiceAccountFile()->getPath(),
			]);
		}

		return $this->client;
	}

	public function getSecret(string $name, string $version = "latest"): ?string
	{
		try {
			$client = $this->getClient();
			$versionName = $client->secretVersionName($this->getProjectId(), $name, $version);
			$response = $client->accessSecretVersion($versionName);

			return $response->getPayload()->getData();
		} catch (\Exception $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			return null;
		}
	}

	public function setSecret(string $name, string $value): string
	{
		(new \Katu\Cache\General(new TIdentifier(__CLASS__, "getSecret", $name, "latest"), new Timeout("1 day")))->clear();

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
