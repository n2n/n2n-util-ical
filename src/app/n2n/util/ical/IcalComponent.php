<?php

namespace n2n\util\ical;

use n2n\util\io\Downloadable;
use n2n\util\HashUtils;

abstract class IcalComponent implements Downloadable {
	const KEY_BEGIN = 'BEGIN';
	const KEY_END = 'END';
	const KEY_VERSION = 'VERSION';
	const KEY_PRODID = 'PRODID';
	const KEY_VALUE_SEPARATOR = ':';
	const VERSION = '2.0';
	const NL = "\r\n";
	private string $productId = '-//HNM N2N//NONSGML Appagic Evagic Helfereinsatz//DE';

	public function getContents(): string {
		$type = $this->getType();
		$contents = $this->wrapIcsLine(self::KEY_BEGIN . self::KEY_VALUE_SEPARATOR . $this->escapeIcsValue($type)) . self::NL;
		$contents .= self::KEY_VERSION . self::KEY_VALUE_SEPARATOR . self::VERSION . self::NL;
		$contents .= $this->wrapIcsLine(self::KEY_PRODID . self::KEY_VALUE_SEPARATOR . $this->escapeIcsValue($this->productId)) . self::NL;
		foreach ($this->getProperties() as $key => $value) {
			if (empty($key) || empty($value)) {
				continue;
			}
			$contents .= $this->wrapIcsLine($key . self::KEY_VALUE_SEPARATOR . $this->escapeIcsValue($value)) . self::NL;
		}
		$contents .= $this->wrapIcsLine(self::KEY_END . self::KEY_VALUE_SEPARATOR . $this->escapeIcsValue($type)) . self::NL;
		return $contents;
	}

	private function escapeIcsValue(string $text): string {
		// escape special chars
		return str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\;', '\,', '\\n', ''], $text);
	}

	private function wrapIcsLine(string $line): string {
		// soft linebreak after 75 chars with Folding (RFC 5545)
		return wordwrap($line, 75, "\r\n ", true);
	}

	public function setProductId(string $productId): static {
		$this->productId = $productId;
		return $this;
	}

	public abstract function getType(): string;

	public abstract function getProperties(): array;

	public function __toString() {
		return $this->getContents();
	}

	function getName(): string {
		return 'events.ics';
	}

	public function getMimeType(): string {
		return 'text/calendar';
	}

	public function getSize(): int {
		return strlen($this->getContents());
	}

	public function getLastModified(): ?\DateTime {
		return null;
	}

	public function buildHash(): string {
		return HashUtils::base36Sha256Hash($this->getContents());
	}

	public function out(): void {
		echo $this->getContents();
	}


}