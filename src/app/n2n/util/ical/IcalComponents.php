<?php

namespace n2n\util\ical;

use n2n\util\ical\impl\IcalEvent;
use n2n\util\ical\impl\IcalCalendar;

class IcalComponents {

	static function calendar(IcalEvent ...$events): IcalCalendar {
		return new IcalCalendar($events);
	}

	static function event(string $uid, \DateTimeImmutable $dateStart, ?\DateTimeImmutable $dateEnd = null,
			?string $summary = null, bool $allDay = true): IcalEvent {
		return (new IcalEvent($uid, $dateStart, $dateEnd, $allDay))->setSummary($summary);
	}

	public static function eventWithoutTime(string $uid, \DateTimeImmutable $dateStart,
			?\DateTimeImmutable $dateEnd = null, ?string $summary = null): IcalEvent {
		return (new IcalEvent($uid, $dateStart, $dateEnd, true))
				->setSummary($summary);
	}

	public static function eventWithTime(string $uid, \DateTimeImmutable $dateTimeStart,
			?\DateTimeImmutable $dateTimeEnd = null, ?string $summary = null): IcalEvent {
		return (new IcalEvent($uid, $dateTimeStart, $dateTimeEnd, false))
				->setSummary($summary);
	}
}