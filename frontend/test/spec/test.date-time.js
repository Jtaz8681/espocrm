/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

describe('date-time', () => {
    let dateTime;

    beforeEach(done => {
		require('date-time', DateTime => {
			dateTime = new DateTime();
			done();
		});
	});

	it("should convert date from display format", () => {
		dateTime.dateFormat = 'MM/DD/YYYY';
		expect(dateTime.fromDisplayDate('10/05/2013')).toBe('2013-10-05');

		dateTime.dateFormat = 'DD.MM.YYYY';
		expect(dateTime.fromDisplayDate('05.10.2013')).toBe('2013-10-05');

		dateTime.dateFormat = 'YYYY-MM-DD';
		expect(dateTime.fromDisplayDate('2013-10-05')).toBe('2013-10-05');
	});

	it("should convert date to display format", () => {
		dateTime.dateFormat = 'MM/DD/YYYY';
		expect(dateTime.toDisplayDate('2013-10-05')).toBe('10/05/2013');

		dateTime.dateFormat = 'DD.MM.YYYY';
		expect(dateTime.toDisplayDate('2013-10-05')).toBe('05.10.2013');

		dateTime.dateFormat = 'YYYY-MM-DD';
		expect(dateTime.toDisplayDate('2013-10-05')).toBe('2013-10-05');
	});

	it("should convert date/time from display format and consider timezone", () => {
		dateTime.dateFormat = 'MM/DD/YYYY';
		dateTime.timeFormat = 'hh:mm A';
		expect(dateTime.fromDisplay('10/05/2013 02:45 PM')).toBe('2013-10-05 14:45:00');

		dateTime.dateFormat = 'MM/DD/YYYY';
		dateTime.timeFormat = 'hh:mm a';
		expect(dateTime.fromDisplay('10/05/2013 02:45 pm')).toBe('2013-10-05 14:45:00');

		dateTime.dateFormat = 'DD.MM.YYYY';
		dateTime.timeFormat = 'HH:mm';
		expect(dateTime.fromDisplay('05.10.2013 14:45')).toBe('2013-10-05 14:45:00');

		dateTime.timeZone = 'Europe/Kiev';
		expect(dateTime.fromDisplay('05.10.2013 17:45')).toBe('2013-10-05 14:45:00');
	});

	it("should convert date/time to display format and consider timezone", () => {
		dateTime.dateFormat = 'MM/DD/YYYY';
		dateTime.timeFormat = 'hh:mm A';
		expect(dateTime.toDisplay('2013-10-05 14:45')).toBe('10/05/2013 02:45 PM');

		dateTime.dateFormat = 'MM/DD/YYYY';
		dateTime.timeFormat = 'hh:mm a';
		expect(dateTime.toDisplay('2013-10-05 14:45')).toBe('10/05/2013 02:45 pm');

		dateTime.dateFormat = 'DD.MM.YYYY';
		dateTime.timeFormat = 'HH:mm';
		expect(dateTime.toDisplay('2013-10-05 14:45')).toBe('05.10.2013 14:45');

		dateTime.timeZone = 'Europe/Kiev';
		expect(dateTime.toDisplay('2013-10-05 14:45')).toBe('05.10.2013 17:45');
	});
});
