<?php

namespace Katu\Config;

class SolrConnectionConfigCollection extends \ArrayObject
{
	public function filterByCollection(string $collection): SolrConnectionConfigCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (SolrConnectionConfig $solrConnectionConfig) use ($collection) {
			return $solrConnectionConfig->getCollection() == $collection;
		})));
	}

	public function getFirst(): ?SolrConnectionConfig
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}
}
