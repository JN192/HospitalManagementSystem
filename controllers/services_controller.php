<?php
require_once __DIR__ . '/../models/Service.php';

$editService = null;
$errors = [];

// Add / edit service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'service_name' => trim($_POST['service_name'] ?? ''),
        'category'     => trim($_POST['category'] ?? ''),
        'rate'         => trim($_POST['rate'] ?? ''),
    ];
    $serviceId = (int)($_POST['service_id'] ?? 0);

    if ($data['service_name'] === '') { $errors[] = 'Service name is required.'; }
    if ($data['category'] === '')     { $errors[] = 'Category is required.'; }
    if ($data['rate'] === '') {
        $errors[] = 'Rate is required.';
    } elseif (!is_numeric($data['rate']) || (float)$data['rate'] < 0) {
        $errors[] = 'Rate must be a valid number that is not negative.';
    }
    if (empty($errors) && Service::nameExists($data['service_name'], $serviceId)) {
        $errors[] = 'A service with this name already exists.';
    }

    if (empty($errors)) {
        if ($serviceId > 0) {
            Service::update($serviceId, $data);
        } else {
            Service::create($data);
        }
        header('Location: index.php?page=services');
        exit;
    }
    // keep the form filled in when there are errors
    $editService = $data + ['service_id' => $serviceId];
}

// Quick set rate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_rate') {
    $rate = trim($_POST['rate'] ?? '');
    $id = (int)($_POST['service_id'] ?? 0);
    if ($rate === '' || !is_numeric($rate) || (float)$rate < 0) {
        $errors[] = 'Rate must be a valid number that is not negative.';
    } else {
        Service::setRate($id, $rate);
        header('Location: index.php?page=services');
        exit;
    }
}

// Edit / activate / deactivate
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'deactivate') {
        Service::setStatus($id, 'Inactive');
        header('Location: index.php?page=services'); exit;
    }
    if ($_GET['action'] === 'activate') {
        Service::setStatus($id, 'Active');
        header('Location: index.php?page=services'); exit;
    }
    if ($_GET['action'] === 'edit') {
        $editService = Service::getById($id);
    }
}

$search = trim($_GET['search'] ?? '');
$services = Service::getAll($search);

require_once __DIR__ . '/../views/services.php';