<?php

namespace Katu\Config\ThirdParty\Google;

use Katu\Files\File;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

abstract class SecretManagerConfig extends \Katu\Config\Config
{
	abstract public function getServiceAccountFile(): File;

	public function getProjectId(): string
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->getServiceAccountFile()->get())["project_id"];
	}

	public function getSecret(string $secret, string $version = "latest"): string
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, $secret, $version), new Timeout("1 day"), function () use ($secret, $version) {
			try {
				$client = new \Google\Cloud\SecretManager\V1\SecretManagerServiceClient([
					"credentials" => $this->getServiceAccountFile()->getPath(),
				]);

				$name = $client->secretVersionName($this->getProjectId(), $secret, $version);
				$response = $client->accessSecretVersion($name);
				$data = $response->getPayload()->getData();

				return $data;
			} catch (\Exception $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

				return null;
			}
		});
	}
}
