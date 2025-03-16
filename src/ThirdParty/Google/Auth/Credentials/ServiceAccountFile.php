<?php

namespace Katu\ThirdParty\Google\Auth\Credentials;

class ServiceAccountFile extends \Katu\Files\File
{
	public function getArray(): array
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->get());
	}

	public function getClientEmail(): ?string
	{
		return $this->getArray()["client_email"] ?? null;
	}

	public function getProjectId(): ?string
	{
		return $this->getArray()["project_id"] ?? null;
	}

	public function getClientConfig(): array
	{
		return [
			"credentials" => $this->getArray(),
			"keyFilePath" => $this->getPath(),
			"projectId" => $this->getProjectId(),
		];
	}
}
