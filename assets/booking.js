(function () {
    'use strict';

    var form = document.querySelector('[data-appointment-form]');
    if (!form) return;

    var doctorSelect = form.querySelector('[name="doctor_id"]');
    var dateInput = form.querySelector('[name="appointment_date"]');
    var timeSelect = form.querySelector('[name="appointment_time"]');
    var summary = form.querySelector('[data-availability-summary]');
    var submit = form.querySelector('[type="submit"]');
    var savedTime = timeSelect.getAttribute('data-selected-time') || '';
    var weekdayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function toMinutes(value) {
        var cleaned = value.trim().toUpperCase();
        var match = cleaned.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/);
        if (!match) return null;
        var hour = parseInt(match[1], 10);
        var minute = parseInt(match[2], 10);
        var suffix = match[3];
        if (suffix === 'AM' && hour === 12) hour = 0;
        if (suffix === 'PM' && hour !== 12) hour += 12;
        return hour * 60 + minute;
    }

    function toTimeValue(minutes) {
        var hour = Math.floor(minutes / 60);
        var minute = minutes % 60;
        return String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
    }

    function toDisplayTime(minutes) {
        var hour = Math.floor(minutes / 60);
        var minute = minutes % 60;
        var suffix = hour >= 12 ? 'PM' : 'AM';
        var displayHour = hour % 12 || 12;
        return displayHour + ':' + String(minute).padStart(2, '0') + ' ' + suffix;
    }

    function currentOption() {
        return doctorSelect.options[doctorSelect.selectedIndex];
    }

    function setSummary(className, title, detail) {
        summary.className = className;
        summary.textContent = '';
        var strong = document.createElement('strong');
        var span = document.createElement('span');
        strong.textContent = title;
        span.textContent = detail;
        summary.appendChild(strong);
        summary.appendChild(span);
    }

    function populateTimes() {
        var option = currentOption();
        timeSelect.innerHTML = '<option value="">-- Choose an available time --</option>';
        if (!option || !option.value) {
            timeSelect.disabled = true;
            return;
        }

        var range = (option.dataset.time || '').split(/\s*-\s*/);
        var start = range.length === 2 ? toMinutes(range[0]) : null;
        var end = range.length === 2 ? toMinutes(range[1]) : null;
        if (start === null || end === null) {
            timeSelect.disabled = true;
            return;
        }

        timeSelect.disabled = false;
        var now = new Date();
        var today = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        var currentMinutes = now.getHours() * 60 + now.getMinutes();
        for (var minutes = start; minutes <= end; minutes += 30) {
            if (dateInput.value === today && minutes <= currentMinutes) continue;
            var value = toTimeValue(minutes);
            var item = document.createElement('option');
            item.value = value;
            item.textContent = toDisplayTime(minutes);
            if (value === savedTime.substring(0, 5)) item.selected = true;
            timeSelect.appendChild(item);
        }
    }

    function validateDate() {
        var option = currentOption();
        if (!option || !option.value || !dateInput.value) {
            dateInput.setCustomValidity('');
            return true;
        }

        var allowedDays = (option.dataset.days || '').split(',').map(function (day) {
            return day.trim();
        });
        var parts = dateInput.value.split('-').map(Number);
        var chosenDate = new Date(parts[0], parts[1] - 1, parts[2]);
        var chosenDay = weekdayNames[chosenDate.getDay()];
        if (allowedDays.indexOf(chosenDay) === -1) {
            var message = 'This doctor is not available on ' + chosenDay + '. Choose ' + option.dataset.schedule + '.';
            dateInput.setCustomValidity(message);
            setSummary('availability-panel availability-error', 'Unavailable date', message);
            submit.disabled = true;
            return false;
        }

        dateInput.setCustomValidity('');
        submit.disabled = false;
        showSchedule();
        return true;
    }

    function showSchedule() {
        var option = currentOption();
        if (!option || !option.value) {
            setSummary('availability-panel', 'Select a doctor', "The doctor's available days and appointment times will appear here.");
            submit.disabled = false;
            return;
        }
        setSummary('availability-panel availability-ok', option.dataset.doctorName,
            'Available ' + option.dataset.schedule + ', ' + option.dataset.time + '. Choose a matching date and time below.');
    }

    doctorSelect.addEventListener('change', function () {
        savedTime = '';
        populateTimes();
        showSchedule();
        validateDate();
    });
    dateInput.addEventListener('change', function () {
        populateTimes();
        validateDate();
    });
    form.addEventListener('submit', function (event) {
        if (!validateDate()) {
            event.preventDefault();
            dateInput.reportValidity();
        }
    });

    populateTimes();
    showSchedule();
    validateDate();
})();
