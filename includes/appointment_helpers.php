<?php

/**
 * Convert a doctor's saved day text (for example "Mon, Wed, Fri" or
 * "Mon - Fri") into PHP's three-letter weekday names.
 */
function parse_available_days($value)
{
    $dayOrder = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $aliases = [
        'sun' => 'Sun', 'sunday' => 'Sun',
        'mon' => 'Mon', 'monday' => 'Mon',
        'tue' => 'Tue', 'tues' => 'Tue', 'tuesday' => 'Tue',
        'wed' => 'Wed', 'wednesday' => 'Wed',
        'thu' => 'Thu', 'thur' => 'Thu', 'thurs' => 'Thu', 'thursday' => 'Thu',
        'fri' => 'Fri', 'friday' => 'Fri',
        'sat' => 'Sat', 'saturday' => 'Sat',
    ];

    $value = strtolower(trim((string)$value));
    if ($value === '') {
        return [];
    }

    if (preg_match('/^([a-z]+)\s*-\s*([a-z]+)$/', $value, $matches)) {
        $start = $aliases[$matches[1]] ?? null;
        $end = $aliases[$matches[2]] ?? null;
        if ($start && $end) {
            $startIndex = array_search($start, $dayOrder, true);
            $endIndex = array_search($end, $dayOrder, true);
            $days = [];
            $index = $startIndex;
            do {
                $days[] = $dayOrder[$index];
                if ($index === $endIndex) {
                    break;
                }
                $index = ($index + 1) % count($dayOrder);
            } while ($index !== $startIndex);
            return $days;
        }
    }

    $days = [];
    foreach (preg_split('/\s*,\s*/', $value) as $part) {
        if (isset($aliases[$part])) {
            $days[] = $aliases[$part];
        }
    }
    return array_values(array_unique($days));
}

/** Return the schedule's start and end as 24-hour H:i strings. */
function parse_available_time_range($value)
{
    if (!preg_match('/^\s*(.+?)\s*-\s*(.+?)\s*$/', (string)$value, $matches)) {
        return null;
    }

    $start = parse_schedule_time($matches[1]);
    $end = parse_schedule_time($matches[2]);
    if ($start === null || $end === null || $end < $start) {
        return null;
    }
    return [$start, $end];
}

/** Parse common 12-hour and 24-hour schedule formats without accepting invalid times. */
function parse_schedule_time($value)
{
    $value = strtoupper(trim((string)$value));
    foreach (['!g:i A', '!g A', '!H:i'] as $format) {
        $parsed = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        $isStrict = $errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0);
        if ($parsed && $isStrict) {
            return $parsed->format('H:i');
        }
    }
    return null;
}

function get_active_doctor($conn, $doctorId)
{
    $stmt = $conn->prepare("SELECT id, name, specialization, available_day, available_time, status FROM doctors WHERE id = ? AND status = 'active'");
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows === 1 ? $result->fetch_assoc() : null;
}

/**
 * Server-side schedule validation. JavaScript improves the form, but this
 * check is authoritative and prevents unavailable bookings.
 */
function doctor_is_available($doctor, $date, $time, &$message)
{
    $dateObject = DateTime::createFromFormat('Y-m-d', (string)$date);
    $validDate = $dateObject && $dateObject->format('Y-m-d') === $date;
    if (!$validDate || $date < date('Y-m-d')) {
        $message = 'Please choose today or a future date.';
        return false;
    }

    $availableDays = parse_available_days($doctor['available_day']);
    $selectedDay = $dateObject->format('D');
    if (!in_array($selectedDay, $availableDays, true)) {
        $message = $doctor['name'] . ' is available on ' . $doctor['available_day'] . ' only.';
        return false;
    }

    $selectedTime = normalise_appointment_time($time);
    $range = parse_available_time_range($doctor['available_time']);
    if ($selectedTime === '' || !$range) {
        $message = 'The selected doctor does not have a valid time schedule.';
        return false;
    }

    if ($selectedTime < $range[0] || $selectedTime > $range[1]) {
        $message = $doctor['name'] . ' is available between ' . $doctor['available_time'] . '.';
        return false;
    }

    $selectedMinutes = ((int)substr($selectedTime, 0, 2) * 60) + (int)substr($selectedTime, 3, 2);
    $startMinutes = ((int)substr($range[0], 0, 2) * 60) + (int)substr($range[0], 3, 2);
    if (($selectedMinutes - $startMinutes) % 30 !== 0) {
        $message = 'Please choose one of the available 30-minute appointment times.';
        return false;
    }

    if ($date === date('Y-m-d') && $selectedTime <= date('H:i')) {
        $message = 'Please choose a future appointment time.';
        return false;
    }

    return true;
}

function appointment_slot_has_clash($conn, $doctorId, $date, $time, $excludeAppointmentId = 0)
{
    if ($excludeAppointmentId > 0) {
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled' AND id != ?");
        $stmt->bind_param('issi', $doctorId, $date, $time, $excludeAppointmentId);
    } else {
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
        $stmt->bind_param('iss', $doctorId, $date, $time);
    }
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function normalise_appointment_time($time)
{
    $time = trim((string)$time);
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time)) {
        return '';
    }
    return substr($time, 0, 5);
}
