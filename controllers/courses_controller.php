<?php
require_once __DIR__ . '/../models/Course.php';

$editCourse = null;
$errors = [];

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $data = [
        'course_code' => strtoupper(trim($_POST['course_code'] ?? '')),
        'course_name' => trim($_POST['course_name'] ?? ''),
    ];
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($data['course_code'] === '') {
        $errors[] = 'Course code is required.';
    } elseif (!preg_match('/^[A-Z0-9-]{2,20}$/', $data['course_code'])) {
        $errors[] = 'Course code may only contain letters, numbers and dashes (2 to 20 characters).';
    }
    if ($data['course_name'] === '') {
        $errors[] = 'Course name is required.';
    }
    if (empty($errors) && Course::exists($data['course_code'], $data['course_name'], $courseId)) {
        $errors[] = 'A course with this code or name already exists.';
    }

    if (empty($errors)) {
        if ($courseId > 0) {
            Course::update($courseId, $data);
            header('Location: index.php?page=courses&saved=updated');
        } else {
            Course::create($data);
            header('Location: index.php?page=courses&saved=created');
        }
        exit;
    }
    $editCourse = $data + ['course_id' => $courseId];
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['course_id'] ?? 0);
    $count = Course::patientCount($id);
    if ($count > 0) {
        $errors[] = 'This course is assigned to ' . $count . ' patient record(s), so it cannot be deleted.';
    } else {
        Course::delete($id);
        header('Location: index.php?page=courses&saved=deleted');
        exit;
    }
}

// Load a course into the form for editing
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $editCourse = Course::getById((int)$_GET['id']);
}

$search = trim($_GET['search'] ?? '');
$courses = Course::getAll($search);

require_once __DIR__ . '/../views/courses.php';