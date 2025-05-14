<?php

namespace n2n\util\ical;

use n2n\util\ical\impl\IcalEvent;

class IcalComponents {

	public static function eventWithoutTime(string $uid, \DateTime $dateStart, ?\DateTime $dateEnd = null, ?string $summary = null): IcalEvent {
		return (new IcalEvent($uid, $dateStart, $dateEnd, true))
				->setSummary($summary);
	}

	public static function eventWithTime(string $uid, \DateTime $dateTimeStart, ?\DateTime $dateTimeEnd = null, ?string $summary = null): IcalEvent {
		return (new IcalEvent($uid, $dateTimeStart, $dateTimeEnd, false))
				->setSummary($summary);
	}
}