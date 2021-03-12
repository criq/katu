<?php

namespace Katu\Files\Formats;

class YAML
{
	public static function encode($var)
	{
		return \Spyc::YAMLDump($var);
	}

	public static function decode($var)
	{
		return \Spyc::YAMLLoad($var);
	}
}
