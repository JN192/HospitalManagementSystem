<?php
require_once __DIR__ . '/../models/Patient.php';

$patientId = (int)($_GET['id'] ?? 0);
$patient = Patient::getById($patientId);

if (!$patient) {
    echo '<h1>Patient not found</h1><p><a href="index.php?page=patients">Back to Patients</a></p>';
    require_once __DIR__ . '/../views/footer.php';
    exit;
}

$admissions = Patient::getAdmissionHistory($patientId);
$treatments = Patient::getTreatmentHistory($patientId);
$bills      = Patient::getBillingHistory($patientId);
$discharges = Patient::getDischargeHistory($patientId);

require_once __DIR__ . '/../views/patient_profile.php';