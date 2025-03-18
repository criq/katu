<?php

namespace Katu\ThirdParty\Google\Auth\Credentials;

class ServiceAccountDirCollection extends \ArrayObject
{
	public function getServiceAccountFiles(): ServiceAccountFileCollection
	{
		return new ServiceAccountFileCollection(array_values(array_unique(array_merge(...array_map(function (ServiceAccountDir $serviceAccountDir) {
			return $serviceAccountDir->getServiceAccountFiles()->getArrayCopy();
		}, $this->getArrayCopy())))));
	}
}
