<?php
require_once __DIR__ . '/../models/Report.php';

$types = [
    'patients'   => 'Patient Report',
    'doctors'    => 'Doctor Report',
    'admissions' => 'Admission Report',
    'treatments' => 'Treatment Report',
    'billing'    => 'Billing Report',
    'discharges' => 'Discharge Report',
];

$type = $_GET['type'] ?? 'patients';
if (!isset($types[$type])) {
    $type = 'patients';
}

$dated = in_array($type, ['admissions', 'treatments', 'billing', 'discharges'], true);
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');
$errors = [];

$validDate = function (string $value): bool {
    $d = DateTime::createFromFormat('!Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
};

if ($dated) {
    if ($from !== '' && !$validDate($from)) {
        $errors[] = 'The "From" date is not valid.';
    }
    if ($to !== '' && !$validDate($to)) {
        $errors[] = 'The "To" date is not valid.';
    }
    if (empty($errors) && $from !== '' && $to !== '' && $from > $to) {
        $errors[] = 'The "From" date cannot be later than the "To" date.';
    }
}

$rows = [];
if (empty($errors)) {
    $f = $from !== '' ? $from : null;
    $t = $to !== '' ? $to : null;

    switch ($type) {
        case 'patients':   $rows = Report::patients(); break;
        case 'doctors':    $rows = Report::doctors(); break;
        case 'admissions': $rows = Report::admissions($f, $t); break;
        case 'treatments': $rows = Report::treatments($f, $t); break;
        case 'billing':    $rows = Report::billing($f, $t); break;
        case 'discharges': $rows = Report::discharges($f, $t); break;
    }
}

require_once __DIR__ . '/../views/reports.php';