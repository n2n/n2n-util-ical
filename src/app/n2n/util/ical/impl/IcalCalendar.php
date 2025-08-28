<?php

namespace n2n\util\ical\impl;

use n2n\util\ical\IcalComponent;
use n2n\util\type\ArgUtils;

class IcalCalendar extends IcalComponent {
	const TYPE = 'VCALENDAR';

	/**
	 * @param IcalEvent[] $events
	 */
	private array $events = [];

	/**
	 * @param IcalEvent[] $events
	 */
	function __construct(array $events = []) {
		$this->setEvents($events);
	}

	/**
	 * @param array $events
	 * @return void
	 */
	function setEvents(array $events) {
		ArgUtils::valArray($events, IcalEvent::class);
		$this->events = $events;
	}

	/**
	 * @return IcalEvent[]
	 */
	function getEvents(): array {
		return $this->events;
	}

	function getType(): string {
		return self::TYPE;

	}

	function getProperties(): array {
		$properties = [];
		foreach ($this->getEvents() as $event) {
			$type = $event->getType();
			$properties[self::KEY_BEGIN] = $type;
			foreach ($event->getProperties() as $key => $value) {
				$properties[$key] = $value;
			}
			$properties[self::KEY_END] = $type;
		}

		return $properties;
	}

}