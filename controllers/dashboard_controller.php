<?php
require_once __DIR__ . '/../models/Dashboard.php';

$summary = Dashboard::getSummary();

require_once __DIR__ . '/../views/dashboard.php';
