<?php
require_once __DIR__ . '/../models/Medicine.php';

$editMedicine = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'medicine_name' => trim($_POST['medicine_name'] ?? ''),
        'dosage_form'   => trim($_POST['dosage_form'] ?? ''),
        'unit_price'    => trim($_POST['unit_price'] ?? ''),
    ];

    foreach (['medicine_name', 'dosage_form', 'unit_price'] as $required) {
        if ($data[$required] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $required)) . ' is required.';
        }
    }
    if ($data['unit_price'] !== '' && !is_numeric($data['unit_price'])) {
        $errors[] = 'Unit price must be a number.';
    }

    if (empty($errors)) {
        $medicineId = $_POST['medicine_id'] ?? '';
        if ($medicineId !== '') {
            Medicine::update((int)$medicineId, $data);
        } else {
            Medicine::create($data);
        }
        header('Location: index.php?page=medicines');
        exit;
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        Medicine::delete($id);
        header('Location: index.php?page=medicines'); exit;
    }
    if ($_GET['action'] === 'edit') {
        $editMedicine = Medicine::getById($id);
    }
}

$search = trim($_GET['search'] ?? '');
$medicines = Medicine::getAll($search);

require_once __DIR__ . '/../views/medicines.php';