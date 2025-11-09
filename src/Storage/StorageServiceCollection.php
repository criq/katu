<?php

namespace Katu\Storage;

class StorageServiceCollection extends \ArrayObject
{
	public function filterByFingerprint(string $fingerprint): StorageServiceCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (StorageService $storageService) use ($fingerprint) {
			return $storageService->getFingerprint() == $fingerprint;
		})));
	}

	public function filterLocal(): StorageServiceCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (StorageService $service) {
			return $service->getIsLocal();
		})));
	}

	public function filterCloud(): StorageServiceCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (StorageService $service) {
			return $service->getIsCloud();
		})));
	}

	public function filterWritable(): StorageServiceCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (StorageService $service) {
			return $service->getIsWritable();
		})));
	}

	public function getObjectFromURI(string $uri): ?StorageObject
	{
		foreach ($this->getArrayCopy() as $service) {
			if (!$service instanceof StorageService) {
				continue;
			}

			if ($service->getIsCompatibleWithURI($uri)) {
				return $service->getObjectByURI($uri);
			}
		}

		return null;
	}

	public function getFirst(): ?StorageService
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getPrimary(): ?StorageService
	{
		return $this->filterWritable()->getFirst();
	}

	public function getPrimaryLocal(): ?StorageService
	{
		return $this->filterLocal()->filterWritable()->getFirst();
	}

	public function getPrimaryCloud(): ?StorageService
	{
		return $this->filterCloud()->filterWritable()->getFirst();
	}
}
