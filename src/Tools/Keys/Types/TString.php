<?php

namespace Katu\Tools\Keys\Types;

class TString extends \Katu\Tools\Keys\Key
{
	public function getParts()
	{
		$parts = new \Katu\Types\TArray;

		if ($this->source instanceof \Katu\Types\TURL || filter_var($this->source, \FILTER_VALIDATE_URL)) {
			try {
				if (is_string($this->source)) {
					$url = new \Katu\Types\TURL($this->source);
				}

				$urlParts = $url->getParts();
				$parts->append($url->getScheme());
				$parts->append(explode('.', $url->getHost()));
				if (isset($urlParts['path'])) {
					$parts->append(explode('/', $urlParts['path']));
				}
				if (isset($urlParts['query'])) {
					$parts->append($this->getHashWithPrefix($urlParts['query']));
				}
			} catch (\Exception $e) {
				$parts->append($this->getSanitizedString($this->source));
			}
		} else {
			$parts->append($this->getSanitizedString($this->source));
		}

		return $parts;
	}
}
