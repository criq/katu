<?php

namespace Katu\ThirdParty\Google\Auth\Credentials;

class ServiceAccountFileCollection extends \ArrayObject
{
	public function filterByClientEmail(string $clientEmail): ServiceAccountFileCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (ServiceAccountFile $file) use ($clientEmail) {
			return $file->getClientEmail() == $clientEmail;
		})));
	}

	public function getFirst(): ?ServiceAccountFile
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}
}
