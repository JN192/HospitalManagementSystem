<?php
require_once __DIR__ . '/../models/Admission.php';

$viewAdmission = null;
$treatments = [];

if (isset($_GET['view_id'])) {
    $viewAdmission = Admission::getByIdFull((int)$_GET['view_id']);
    if ($viewAdmission) {
        $treatments = Admission::getTreatmentsByAdmission((int)$_GET['view_id']);
    }
}

$search = trim($_GET['search'] ?? '');
$admitted = Admission::getCurrentlyAdmitted($search);

require_once __DIR__ . '/../views/admission_monitoring.php';