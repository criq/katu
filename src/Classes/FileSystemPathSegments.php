<?php

namespace Katu\Classes;

class FileSystemPathSegments {

	public $segments = [];

	public function __construct($segments = []) {
		foreach ($segments as $segment) {
			$this->add($segment);
		}
	}

	public function getPathSegments() {
		$segments = [];

		foreach ($this->segments as $segment) {
			$path = $segment->getPathSegments();
			$segments = array_merge($segments, is_array($path) ? $path : [$path]);
		}

		$segments = array_values(array_filter($segments));

		return $segments;
	}

	public function add($segment) {
		if ($segment instanceof FileSystemPathSegment) {
			$this->segments[] = $segment;
		} else {
			$this->segments[] = new FileSystemPathSegment($segment);
		}

		return true;
	}

}
