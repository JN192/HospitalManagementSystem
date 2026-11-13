<?php
$peso = fn($amount) => '₱' . number_format((float)$amount, 2);
$metrics = [
    ['label' => 'Total Patients', 'value' => number_format((int)$summary['total_patients']), 'link' => 'patients'],
    ['label' => 'Admitted Patients', 'value' => number_format((int)$summary['admitted_patients']), 'link' => 'admissions_monitor'],
    ['label' => 'Available Beds', 'value' => number_format((int)$summary['available_beds']), 'link' => 'rooms'],
    ['label' => 'Occupied Beds', 'value' => number_format((int)$summary['occupied_beds']), 'link' => 'rooms'],
    ["label" => "Today's Admissions", 'value' => number_format((int)$summary['todays_admissions']), 'link' => 'admissions'],
    ["label" => "Today's Discharges", 'value' => number_format((int)$summary['todays_discharges']), 'link' => 'discharge'],
];
?>

<section class="dashboard">
    <h1>Hospital Dashboard</h1>
    <p class="dashboard-updated">Live operational summary for <?= htmlspecialchars(date('F j, Y')) ?></p>

    <div class="dashboard-grid">
        <?php foreach ($metrics as $metric): ?>
            <a class="dashboard-card" href="index.php?page=<?= htmlspecialchars($metric['link']) ?>">
                <span class="dashboard-card-label"><?= htmlspecialchars($metric['label']) ?></span>
                <strong class="dashboard-card-value"><?= htmlspecialchars($metric['value']) ?></strong>
            </a>
        <?php endforeach; ?>

        <div class="dashboard-card dashboard-card-balance">
            <span class="dashboard-card-label">Outstanding Bills</span>
            <strong class="dashboard-card-value"><?= htmlspecialchars($peso($summary['outstanding_bills'])) ?></strong>
        </div>
    </div>

    <section class="admission-summary">
        <h2>Today's Admission Summary</h2>
        <div class="admission-summary-grid">
            <div class="admission-summary-item">
                <span>Emergency</span>
                <strong><?= number_format((int)$summary['emergency_admissions']) ?></strong>
            </div>
            <div class="admission-summary-item">
                <span>Regular</span>
                <strong><?= number_format((int)$summary['regular_admissions']) ?></strong>
            </div>
        </div>
    </section>
</section>
