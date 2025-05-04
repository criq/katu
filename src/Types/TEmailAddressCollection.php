<?php

namespace Katu\Types;

class TEmailAddressCollection extends \ArrayObject
{
	public static function createFromString(?string $string): ?TEmailAddressCollection
	{
		$regex = "/(?:[\"\']?(?<name>[^\"\']+)?[\"\']?\s*)?<(?<email_in_brackets>[^>]+)>|(?<email_standalone>[^,\s]+)/";
		preg_match_all($regex, $string, $matches, \PREG_SET_ORDER);

		return new static(array_values(array_filter(array_map(function (array $match) {
			try {
				return new TEmailAddress($match["email_in_brackets"] ?: $match["email_standalone"], $match["name"]);
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
