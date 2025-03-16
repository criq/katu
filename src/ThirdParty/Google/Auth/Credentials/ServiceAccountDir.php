<?php

namespace Katu\ThirdParty\Google\Auth\Credentials;

class ServiceAccountDir extends \Katu\Files\File
{
	public function getServiceAccountFiles(): ServiceAccountFileCollection
	{
		return new ServiceAccountFileCollection(array_map(function (\Katu\Files\File $file) {
			return new ServiceAccountFile($file);
		}, $this->getFiles()->filterByRegex("/\.json$/")->getArrayCopy()));
	}
}
