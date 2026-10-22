USE hms_db;

SELECT 'patients' AS tbl, (SELECT COUNT(*) FROM patients) AS new_rows, (SELECT COUNT(*) FROM patients_old) AS old_rows
UNION ALL SELECT 'doctors',        (SELECT COUNT(*) FROM doctors),        (SELECT COUNT(*) FROM doctors_old)
UNION ALL SELECT 'departments',    (SELECT COUNT(*) FROM departments),    (SELECT COUNT(*) FROM departments_old)
UNION ALL SELECT 'rooms',          (SELECT COUNT(*) FROM rooms),          (SELECT COUNT(*) FROM rooms_old)
UNION ALL SELECT 'services',       (SELECT COUNT(*) FROM services),       (SELECT COUNT(*) FROM services_old)
UNION ALL SELECT 'admissions',     (SELECT COUNT(*) FROM admissions),     (SELECT COUNT(*) FROM admissions_old)
UNION ALL SELECT 'bills',          (SELECT COUNT(*) FROM bills),          (SELECT COUNT(*) FROM bills_old);

SHOW CREATE TABLE patients;