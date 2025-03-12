<?php

namespace Katu\Config;

abstract class SolrConfig extends \Katu\Config\Config
{
	abstract public function getSolrConnectionConfigs(): SolrConnectionConfigCollection;
}
