<?php

namespace Katu\Types;

class TEmailAddressCollection extends \ArrayObject
{
	public static function createFromString(?string $string): ?TEmailAddressCollection
	{
		$emailRegex = '/\s*(?:"([^"]*)"\s*)?(?:<?([^>,\s]+@[^>,\s]+)>?)\s*(?:,|$)/';
		preg_match_all($emailRegex, $string, $matches, PREG_SET_ORDER);

		return new static(array_values(array_filter(array_map(function (array $match) {
			try {
				return new TEmailAddress($match[2] ?: $match[1], $match[1] ?: null);
			} catch (\Throwable $e) {
				// Nevermind.
			}
		}, $matches))));
	}

	public function getEmailAddresses(): array
	{
		return array_values(array_unique(array_map(function (TEmailAddress $tEmailAddress) {
			return $tEmailAddress->getEmailAddress();
		}, $this->getArrayCopy())));
	}
}
