<?php
require_once __DIR__ . '/../models/Room.php';

$editRoom = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'room_number' => trim($_POST['room_number'] ?? ''),
        'room_type'   => trim($_POST['room_type'] ?? ''),
        'bed_number'  => trim($_POST['bed_number'] ?? ''),
        'room_rate'   => trim($_POST['room_rate'] ?? ''),
    ];

    foreach (['room_number', 'room_type', 'bed_number', 'room_rate'] as $required) {
        if ($data[$required] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $required)) . ' is required.';
        }
    }
    if ($data['room_rate'] !== '' && !is_numeric($data['room_rate'])) {
        $errors[] = 'Room rate must be a number.';
    }

    if (empty($errors)) {
        $roomId = $_POST['room_id'] ?? '';
        if ($roomId !== '') {
            Room::update((int)$roomId, $data);
        } else {
            Room::create($data);
        }
        header('Location: index.php?page=rooms');
        exit;
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'occupy') {
        Room::setStatus($id, 'Occupied');
        header('Location: index.php?page=rooms'); exit;
    }
    if ($_GET['action'] === 'available') {
        Room::setStatus($id, 'Available');
        header('Location: index.php?page=rooms'); exit;
    }
    if ($_GET['action'] === 'maintenance') {
        Room::setStatus($id, 'Maintenance');
        header('Location: index.php?page=rooms'); exit;
    }
    if ($_GET['action'] === 'edit') {
        $editRoom = Room::getById($id);
    }
}

$search = trim($_GET['search'] ?? '');
$rooms = Room::getAll($search);

require_once __DIR__ . '/../views/rooms.php';