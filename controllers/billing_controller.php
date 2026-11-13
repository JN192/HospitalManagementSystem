<?php
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Medicine.php';

$errors = [];
$submittedBillId = $_GET['bill_id'] ?? $_POST['bill_id'] ?? null;
$billId = filter_var($submittedBillId, FILTER_VALIDATE_INT);
$billId = $billId !== false && $billId !== null && $billId > 0 ? $billId : 0;
if ($submittedBillId !== null && $billId === 0) {
    $errors[] = 'Choose a valid bill.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

    if ($action === 'generate') {
        $admissionId = filter_var($_POST['admission_id'] ?? null, FILTER_VALIDATE_INT);
        if ($admissionId <= 0) {
            $errors[] = 'Select an admission to bill.';
        } else {
            $existing = Bill::getByAdmission($admissionId);
            if ($existing) {
                // validation: one bill per admission
                header('Location: index.php?page=billing&bill_id=' . $existing['bill_id']);
                exit;
            }
            try {
                $newId = Bill::generate($admissionId);
                header('Location: index.php?page=billing&bill_id=' . $newId . '&generated=1');
                exit;
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
    } elseif ($billId > 0) {
        $current = Bill::getById($billId);

        if (!$current) {
            $errors[] = 'Bill not found.';
        } elseif ($current['status'] === 'Paid') {
            $errors[] = 'This bill is fully paid and can no longer be changed.';
        } else {
            $qty = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
            $qty = $qty === false ? 0 : $qty;
            $back = 'Location: index.php?page=billing&bill_id=' . $billId;

            if ($action === 'add_service') {
                $serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
                $service = $serviceId ? Service::getById($serviceId) : null;
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
                $medicineId = filter_var($_POST['medicine_id'] ?? null, FILTER_VALIDATE_INT);
                $medicine = $medicineId ? Medicine::getById($medicineId) : null;
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
                $descValue = $_POST['description'] ?? '';
                $amountValue = $_POST['amount'] ?? '';
                $desc = is_string($descValue) ? trim($descValue) : '';
                $amount = is_string($amountValue) ? trim($amountValue) : '';
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
                $itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$itemId || $itemId <= 0) {
                    $errors[] = 'Choose a valid bill item to remove.';
                } else {
                    Bill::removeItem($billId, $itemId);
                    header($back);
                    exit;
                }
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