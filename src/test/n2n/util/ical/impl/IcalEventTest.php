<?php

namespace n2n\util\ical\impl;


use PHPUnit\Framework\TestCase;
use n2n\util\ical\IcalComponents;
use n2n\util\io\ob\OutputBuffer;

class IcalEventTest extends TestCase {

	private function propValue(array $properties, string $key): ?string {
		foreach ($properties as $property) {
			if ($property->key === $key) {
				return $property->value;
			}
		}

		return null;
	}

	public function testIcalComponentsWithoutTimeNoDateEnd() {
		$icalEvent = IcalComponents::eventWithoutTime('uidString', new \DateTimeImmutable('2025-05-02T05:02:00'));
		$this->assertEquals(new \DateTimeImmutable('2025-05-02T05:02:00'), $icalEvent->getStartDate());
		$this->assertNull($icalEvent->getEndDate());

		$this->assertEquals((new \DateTimeImmutable('2025-05-02T05:02:00'))->format('Ymd'),
				$this->propValue($icalEvent->getProperties(), 'DTSTART'));
		$this->assertEquals((new \DateTimeImmutable('2025-05-02T05:02:00'))->add(new \DateInterval('P1D'))->format('Ymd'),
				$this->propValue($icalEvent->getProperties(), 'DTEND'));
	}

	public function testIcalComponentsWithTimeNoDateEnd() {
		$icalEvent = IcalComponents::eventWithTime('uidString', new \DateTimeImmutable('2025-05-02T05:02:00'));
		$this->assertEquals(new \DateTimeImmutable('2025-05-02T05:02:00'), $icalEvent->getStartDate());
		$this->assertNull($icalEvent->getEndDate());

		$this->assertEquals((new \DateTimeImmutable('2025-05-02T05:02:00'))->format('Ymd\THis'), $this->propValue($icalEvent->getProperties(), 'DTSTART'));
		$this->assertEquals((new \DateTimeImmutable('2025-05-02T05:02:00'))->format('Ymd\THis'), $this->propValue($icalEvent->getProperties(), 'DTEND'));
	}

	public function testIcalComponentsWithoutTimeDateEnd() {
		$icalEvent = IcalComponents::eventWithoutTime('uidString', new \DateTimeImmutable('2025-05-02T05:02:00'), new \DateTimeImmutable('2025-06-03T05:02:00'));
		$this->assertEquals(new \DateTimeImmutable('2025-05-02T05:02:00'), $icalEvent->getStartDate());
		$this->assertEquals(new \DateTimeImmutable('2025-06-03T05:02:00'), $icalEvent->getEndDate());

		$this->assertEquals((new \DateTimeImmutable('2025-05-02T05:02:00'))->format('Ymd'), $this->propValue($icalEvent->getProperties(), 'DTSTART'));
		$this->assertEquals((new \DateTimeImmutable('2025-06-04T05:02:00'))->format('Ymd'), $this->propValue($icalEvent->getProperties(), 'DTEND'));
	}

	public function testIcalComponentsWithTimeDateEnd() {
		$icalEvent = IcalComponents::eventWithTime('uidString', new \DateTimeImmutable('2025-05-02T05:02:00'), new \DateTimeImmutable('2025-06-03T01:04:00'), 'summary');
		$this->assertEquals(new \DateTimeImmutable('2025-05-02T05:02:00'), $icalEvent->getStartDate());
		$this->assertEquals(new \DateTimeImmutable('2025-06-03T01:04:00'), $icalEvent->getEndDate());

		$this->assertEquals((new \DateTimeImmutable('2025-05-02T05:02:00'))->format('Ymd\THis'), $this->propValue($icalEvent->getProperties(), 'DTSTART'));
		$this->assertEquals((new \DateTimeImmutable('2025-06-03T01:04:00'))->format('Ymd\THis'), $this->propValue($icalEvent->getProperties(), 'DTEND'));
	}

	/**
	 * @throws \Exception
	 */
	public function testIcalComponentsWithTimeAndSetTimezone() {
		$icalEvent = IcalComponents::eventWithTime('uidString', (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Zurich')))->add(new \DateInterval('P14D'))
				->setTime(5, 55, 55))->setIncludeTimeZone(true);

		$this->assertStringContainsString((new \DateTimeImmutable('now', new \DateTimeZone('Europe/Zurich')))->add(new \DateInterval('P14D'))
				->setTime(5, 55, 55)->setTimezone(new \DateTimeZone('UTC'))->format("Ymd\THis\Z"), $this->propValue($icalEvent->getProperties(), 'DTSTART'));
		$this->assertStringContainsString((new \DateTimeImmutable('now', new \DateTimeZone('Europe/Zurich')))->add(new \DateInterval('P14D'))
				->setTime(5, 55, 55)->setTimezone(new \DateTimeZone('UTC'))->format("Ymd\THis\Z"), $this->propValue($icalEvent->getProperties(), 'DTEND'));
	}

	public function testSetterAndGetter() {
		$icalEvent = new IcalEvent('uidString', new \DateTimeImmutable('2025-05-02T05:02:00'));

		$icalEvent->setUid('uid')
				->setStartDate(new \DateTimeImmutable('2025-05-02T05:08:00'))
				->setEndDate(new \DateTimeImmutable('2025-05-02T05:18:00'))
				->setIncludeTimeZone(true)
				->setTimeOmitted(true)
				->setUrl('https://appagic.test')
				->setDescription('this is a description')
				->setSummary('this is a summary')
				->setLocation('location text');
		$icalCalendar = $icalEvent->toCalendar()->setProductId('product id');

		$this->assertEquals('uid', $icalEvent->getUid());
		$this->assertEquals('2025-05-02T05:08:00', $icalEvent->getStartDate()->format('Y-m-d\TH:i:s'));
		$this->assertEquals('2025-05-02T05:18:00', $icalEvent->getEndDate()->format('Y-m-d\TH:i:s'));
		$this->assertTrue($icalEvent->isIncludeTimeZone());
		$this->assertTrue($icalEvent->isTimeOmitted());
		$this->assertEquals('https://appagic.test', $icalEvent->getUrl());
		$this->assertEquals('this is a description', $icalEvent->getDescription());
		$this->assertEquals('this is a summary', $icalEvent->getSummary());
		$this->assertEquals('location text', $icalEvent->getLocation());

		$this->assertEquals('uid', $this->propValue($icalEvent->getProperties(), 'UID'));
		$this->assertEquals($icalEvent->getUrl(), $this->propValue($icalEvent->getProperties(), 'URL'));
		$this->assertEquals($icalEvent->getDescription(), $this->propValue($icalEvent->getProperties(), 'DESCRIPTION'));
		$this->assertEquals($icalEvent->getSummary(), $this->propValue($icalEvent->getProperties(), 'SUMMARY'));
		$this->assertEquals($icalEvent->getLocation(), $this->propValue($icalEvent->getProperties(), 'LOCATION'));

		$this->assertEquals('text/calendar', $icalEvent->getMimeType());
		$this->assertEquals(strlen($icalEvent->getContents()), $icalEvent->getSize());
		$this->assertStringContainsString('product id', $icalCalendar->getContents());
	}

	function testIcalEventOut(): void {
		$ob = new OutputBuffer();
		$ob->start();
		$icalEvent = new IcalEvent('uidString', new \DateTimeImmutable('2025-05-02T05:02:00'));
		$icalEvent->setDescription('this is a description, this is a description 
		this is a description; this is a description this is a description this is a description this is a description');
		$icalEvent->out();
		$ob->end();

		$content = $ob->get();
		$this->assertEquals($icalEvent->getContents(), $content);
	}
}
