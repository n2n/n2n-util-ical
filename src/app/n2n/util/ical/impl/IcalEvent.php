<?php

namespace n2n\util\ical\impl;

use n2n\util\uri\Url;
use n2n\util\ical\IcalComponent;

class IcalEvent extends IcalComponent {

	const TYPE = 'VEVENT';

	const KEY_SUMMARY = 'SUMMARY';
	const KEY_DESCRIPTION = 'DESCRIPTION';
	const KEY_LOCATION = 'LOCATION';
	const KEY_UID = 'UID';
	const KEY_DTSTAMP = 'DTSTAMP';
	const KEY_DTSTART = 'DTSTART';
	const KEY_DTEND = 'DTEND';
	const KEY_URL = 'URL';

	private string $uid;
	private \DateTime $dateStart;
	private ?\DateTime $dateEnd;
	private ?string $summary = null;
	private ?string $description = null;
	private ?string $location = null;
	private ?Url $url = null;
	private bool $includeTimezone = false;

	public function __construct(string $uid, \DateTime $dateStart, ?\DateTime $dateEnd = null, private bool $timeOmitted = true) {
		$this->uid = $uid;
		$this->dateStart = $dateStart;

		if (null !== $dateEnd) {
			$this->dateEnd = $dateEnd;
			if ($this->timeOmitted) {
				$this->dateEnd->modify('+1 day');
			}
		} else if (!$timeOmitted) {
			$this->dateEnd = clone $dateStart;
			$this->dateEnd->setTime(23, 59, 59);
		} else {
			$this->dateEnd = clone $dateStart;
			$this->dateEnd->modify('+1 day');
		}
	}

	public function getUid(): string {
		return $this->uid;
	}

	public function setUid(string $uid): static {
		$this->uid = $uid;
		return $this;
	}

	public function getDateStart(): \DateTime {
		return $this->dateStart;
	}

	public function setDateStart(\DateTime $dateStart): static {
		$this->dateStart = $dateStart;
		return $this;
	}

	public function getDateEnd(): ?\DateTime {
		return $this->dateEnd;
	}

	public function setDateEnd(\DateTime $dateEnd): static {
		$this->dateEnd = $dateEnd;
		return $this;
	}

	public function getSummary(): ?string {
		return $this->summary;
	}

	public function setSummary(?string $summary): static {
		$this->summary = $summary;
		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(?string $description): static {
		$this->description = $description;
		return $this;
	}

	public function getLocation(): ?string {
		return $this->location;
	}

	public function setLocation(?string $location): static {
		$this->location = $location;
		return $this;
	}

	public function setIncludeTimeZone(bool $includeTimeZone): static {
		$this->includeTimezone = $includeTimeZone;
		return $this;
	}

	public function isIncludeTimeZone(): bool {
		return $this->includeTimezone;
	}

	public function getUrl(): ?Url {
		return $this->url;
	}

	public function setUrl(null|string|Url $url): static {
		if (is_string($url)) {
			$url = Url::build($url);
		}
		$this->url = $url;
		return $this;
	}

	/**
	 * will influence getProperties DTSTART and DTEND, if false only date is used and Time is ignored
	 * don't influence getDateStart and getDateEnd
	 */
	public function setTimeOmitted(bool $timeOmitted): static {
		$this->timeOmitted = $timeOmitted;
		return $this;
	}

	public function isTimeOmitted(): bool {
		return $this->timeOmitted;
	}

	function getType(): string {
		return self::TYPE;
	}

	public function getProperties(): array {
		$properties = array();

		if (null !== $this->summary) {
			$properties[self::KEY_SUMMARY] = $this->summary;
		}

		if (null !== $this->description) {
			$properties[self::KEY_DESCRIPTION] = $this->description;
		}

		if (null !== $this->location) {
			$properties[self::KEY_LOCATION] = $this->location;
		}

		$properties[self::KEY_UID] = $this->uid;

		if (null !== $this->url) {
			$properties[self::KEY_URL] = $this->url->__toString();
		}

		$properties[self::KEY_DTSTART] = $this->buildDateTimeValue($this->dateStart);
		$properties[self::KEY_DTEND] = $this->buildDateTimeValue($this->dateEnd);
		$properties[self::KEY_DTSTAMP] = (new \DateTime('now', new \DateTimeZone('UTC')))->format("Ymd\THis\Z");

		return $properties;
	}

	private function buildDateTimeValue(\DateTime $dateTime): string {
		if ($this->isTimeOmitted()) {
			return $dateTime->format('Ymd');
		}
		if ($this->includeTimezone) {
			$utcDateTime = clone $dateTime;
			$utcDateTime->setTimezone(new \DateTimeZone('UTC'));

			return $utcDateTime->format("Ymd\THis\Z");
		}
		return $dateTime->format('Ymd\THis');
	}

}
