USE hms_db;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Keep the current tables as backups
RENAME TABLE
  patients TO patients_old, doctors TO doctors_old, departments TO departments_old,
  rooms TO rooms_old, admissions TO admissions_old, treatments TO treatments_old,
  medicines TO medicines_old, treatment_medicines TO treatment_medicines_old,
  services TO services_old, bills TO bills_old, bill_items TO bill_items_old,
  payments TO payments_old, discharges TO discharges_old;

-- 2. New tables with AUTO_INCREMENT primary keys
CREATE TABLE departments (
  department_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  department_name VARCHAR(100) NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE patients (
  patient_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  patient_code VARCHAR(20) NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  birth_date DATE NOT NULL,
  sex VARCHAR(10) NOT NULL,
  contact_number VARCHAR(20) NOT NULL,
  address VARCHAR(255) NOT NULL,
  emergency_contact VARCHAR(100) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active'
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE doctors (
  doctor_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  doctor_code VARCHAR(20) NOT NULL,
  doctor_name VARCHAR(100) NOT NULL,
  specialization VARCHAR(100) NOT NULL,
  department_id INT NOT NULL,
  contact_number VARCHAR(20) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active',
  CONSTRAINT hms_fk_doctors_department FOREIGN KEY (department_id) REFERENCES departments (department_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
  room_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(20) NOT NULL,
  room_type VARCHAR(50) NOT NULL,
  bed_number VARCHAR(20) NOT NULL,
  room_rate DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Available'
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE medicines (
  medicine_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  medicine_name VARCHAR(100) NOT NULL,
  dosage_form VARCHAR(50) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE services (
  service_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL,
  rate DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active'
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE admissions (
  admission_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  room_id INT NOT NULL,
  admission_date DATE NOT NULL,
  admission_time TIME NOT NULL,
  admission_type VARCHAR(20) NOT NULL,
  reason VARCHAR(255) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Admitted',
  CONSTRAINT hms_fk_adm_patient FOREIGN KEY (patient_id) REFERENCES patients (patient_id),
  CONSTRAINT hms_fk_adm_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (doctor_id),
  CONSTRAINT hms_fk_adm_room FOREIGN KEY (room_id) REFERENCES rooms (room_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE treatments (
  treatment_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admission_id INT NOT NULL,
  doctor_id INT NOT NULL,
  treatment_date DATE NOT NULL,
  diagnosis VARCHAR(255) NOT NULL,
  symptoms TEXT NOT NULL,
  follow_up_instructions TEXT NOT NULL,
  treatment_description TEXT NOT NULL,
  notes TEXT NOT NULL,
  CONSTRAINT hms_fk_treat_admission FOREIGN KEY (admission_id) REFERENCES admissions (admission_id),
  CONSTRAINT hms_fk_treat_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (doctor_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE treatment_medicines (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  treatment_id INT NOT NULL,
  medicine_id INT NOT NULL,
  dosage VARCHAR(100) NOT NULL,
  frequency VARCHAR(100) NOT NULL,
  duration VARCHAR(100) NOT NULL,
  instructions TEXT NOT NULL,
  CONSTRAINT hms_fk_tm_treatment FOREIGN KEY (treatment_id) REFERENCES treatments (treatment_id),
  CONSTRAINT hms_fk_tm_medicine FOREIGN KEY (medicine_id) REFERENCES medicines (medicine_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE bills (
  bill_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admission_id INT NOT NULL,
  bill_date DATE NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL,
  CONSTRAINT hms_fk_bill_admission FOREIGN KEY (admission_id) REFERENCES admissions (admission_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE bill_items (
  bill_item_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  service_id INT NULL,
  description VARCHAR(255) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  CONSTRAINT hms_fk_bi_bill FOREIGN KEY (bill_id) REFERENCES bills (bill_id),
  CONSTRAINT hms_fk_bi_service FOREIGN KEY (service_id) REFERENCES services (service_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  payment_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  amount_paid DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(20) NOT NULL,
  payment_date DATETIME NOT NULL,
  CONSTRAINT hms_fk_pay_bill FOREIGN KEY (bill_id) REFERENCES bills (bill_id)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE discharges (
  discharge_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admission_id INT NOT NULL,
  discharge_date DATE NOT NULL,
  discharge_diagnosis TEXT NOT NULL,
  discharge_instructions TEXT NOT NULL,
  follow_up_date DATE NULL,
  CONSTRAINT hms_fk_dis_admission FOREIGN KEY (admission_id) REFERENCES admissions (admission_id)
) DEFAULT CHARSET=utf8mb4;

-- 3. Copy the existing data across (parents first)
INSERT INTO departments SELECT * FROM departments_old;
INSERT INTO patients SELECT * FROM patients_old;
INSERT INTO doctors SELECT * FROM doctors_old;
INSERT INTO rooms SELECT * FROM rooms_old;
INSERT INTO medicines SELECT * FROM medicines_old;
INSERT INTO services SELECT * FROM services_old;
INSERT INTO admissions SELECT * FROM admissions_old;
INSERT INTO treatments SELECT * FROM treatments_old;
INSERT INTO treatment_medicines SELECT * FROM treatment_medicines_old;
INSERT INTO bills SELECT * FROM bills_old;
INSERT INTO bill_items SELECT * FROM bill_items_old;
INSERT INTO payments SELECT * FROM payments_old;
INSERT INTO discharges SELECT * FROM discharges_old;

SET FOREIGN_KEY_CHECKS = 1;