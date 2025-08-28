<?php

namespace n2n\util\ical;

class IcalProperty {

	function __construct(public string $key, public ?string $value) {

	}

	function isEmpty(): bool {
		return empty($this->key) || empty($this->value);
	}
}