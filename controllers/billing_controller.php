<?php
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Medicine.php';

$errors = [];
$billId = (int)($_GET['bill_id'] ?? $_POST['bill_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate') {
        $admissionId = (int)($_POST['admission_id'] ?? 0);
        if ($admissionId <= 0) {
            $errors[] = 'Select an admission to bill.';
        } else {
            $existing = Bill::getByAdmission($admissionId);
            if ($existing) {
                // validation: one bill per admission
                header('Location: index.php?page=billing&bill_id=' . $existing['bill_id']);
                exit;
            }
            $newId = Bill::generate($admissionId);
            header('Location: index.php?page=billing&bill_id=' . $newId . '&generated=1');
            exit;
        }
    } elseif ($billId > 0) {
        $current = Bill::getById($billId);

        if (!$current) {
            $errors[] = 'Bill not found.';
        } elseif ($current['status'] === 'Paid') {
            $errors[] = 'This bill is fully paid and can no longer be changed.';
        } else {
            $qty  = (int)($_POST['quantity'] ?? 1);
            $back = 'Location: index.php?page=billing&bill_id=' . $billId;

            if ($action === 'add_service') {
                $service = Service::getById((int)($_POST['service_id'] ?? 0));
                if (!$service || $service['status'] !== 'Active') {
                    $errors[] = 'Select an active hospital service.';
                } elseif ($qty < 1) {
                    $errors[] = 'Quantity must be at least 1.';
                } else {
                    Bill::addItem(
                        $billId,
                        (int)$service['service_id'],
                        $service['service_name'] . ' (' . $service['category'] . ') x ' . $qty,
                        (float)$service['rate'] * $qty
                    );
                    header($back);
                    exit;
                }
            } elseif ($action === 'add_medicine') {
                $medicine = Medicine::getById((int)($_POST['medicine_id'] ?? 0));
                if (!$medicine) {
                    $errors[] = 'Select a medicine.';
                } elseif ($qty < 1) {
                    $errors[] = 'Quantity must be at least 1.';
                } else {
                    Bill::addItem(
                        $billId,
                        null,
                        'Medicine: ' . $medicine['medicine_name'] . ' (' . $medicine['dosage_form'] . ') x ' . $qty,
                        (float)$medicine['unit_price'] * $qty
                    );
                    header($back);
                    exit;
                }
            } elseif ($action === 'add_other') {
                $desc   = trim($_POST['description'] ?? '');
                $amount = trim($_POST['amount'] ?? '');
                if ($desc === '') {
                    $errors[] = 'Description is required for other charges.';
                }
                if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
                    $errors[] = 'Amount must be a number greater than zero.';
                }
                if (empty($errors)) {
                    Bill::addItem($billId, null, 'Other: ' . mb_substr($desc, 0, 240), (float)$amount);
                    header($back);
                    exit;
                }
            } elseif ($action === 'remove_item') {
                Bill::removeItem($billId, (int)($_POST['item_id'] ?? 0));
                header($back);
                exit;
            } elseif ($action === 'refresh_room') {
                Bill::refreshRoomCharge($billId);
                header($back);
                exit;
            }
        }
    }
}

$bill = $billId > 0 ? Bill::getById($billId) : null;
$items = $bill ? Bill::getItems($billId) : [];
$services = $bill ? array_filter(Service::getAll(), fn($s) => $s['status'] === 'Active') : [];
$medicines = $bill ? Medicine::getAll() : [];
$admissions = $bill ? [] : Bill::getAdmissionsForBilling();

require_once __DIR__ . '/../views/billing.php';