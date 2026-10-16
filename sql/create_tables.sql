CREATE TABLE Roles (
role_id INT  UNSIGNED NOT NULL AUTO_INCREMENT,
role_name VARCHAR(50) NOT NULL,
PRIMARY KEY (role_id)
);


CREATE TABLE Permissions (
permission_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
permission_desc VARCHAR(50) NOT NULL,
permission_page VARCHAR(150) NOT NULL,
PRIMARY KEY (permission_id)
);


CREATE TABLE Role_permission (
role_id INTEGER UNSIGNED NOT NULL,
permission_id INTEGER UNSIGNED NOT NULL,
FOREIGN KEY (role_id) REFERENCES Roles(role_id),
FOREIGN KEY (permission_id) REFERENCES Permissions(permission_id)
);


CREATE TABLE Hospital (
hospital_id VARCHAR(36) NOT NULL,
hospital_name VARCHAR(255) NOT NULL,
hospital_city VARCHAR(255) NOT NULL,
hospital_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (hospital_id)
);


CREATE TABLE System_user (
user_id CHAR(36) NOT NULL,
user_username VARCHAR(100),
user_password_hash VARCHAR(255) NULL,
user_name VARCHAR(100) NOT NULL,
user_surname VARCHAR(100) NOT NULL,
user_email VARCHAR(255),
user_phone_number VARCHAR(30),
role_id INT UNSIGNED NOT NULL,
user_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
user_verification_token_hash CHAR(64) NULL,
user_status ENUM('pending', 'active', 'deactivated') NOT NULL DEFAULT 'pending',
hospital_id VARCHAR(36) NOT NULL,
PRIMARY KEY (user_id),
FOREIGN KEY (role_id) REFERENCES Roles(role_id),
FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id),
UNIQUE (user_username),
UNIQUE (user_email),
UNIQUE (user_phone_number)
);

CREATE TABLE Department(
department_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
department_name VARCHAR(255) NOT NULL,
hospital_id VARCHAR(36) NOT NULL,
department_status ENUM('active', 'deactivated') NOT NULL DEFAULT 'active',
PRIMARY KEY (department_id),
FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
);

ALTER TABLE Department
ADD UNIQUE KEY (department_name,hospital_id);


CREATE TABLE Pathogen (
pathogen_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
pathogen_name VARCHAR(100) NOT NULL,
pathogen_type ENUM('bacterium', 'virus', 'fungus', 'parasite') NOT NULL,
pathogen_transmission ENUM('contact', 'aerosol', 'droplet', 'vectorborne', 'bloodborne', 'fecal-oral') NOT NULL,
pathogen_risk_category TINYINT UNSIGNED NOT NULL,
pathogen_dangerous BOOLEAN NOT NULL DEFAULT FALSE,
PRIMARY KEY (pathogen_id),
UNIQUE (pathogen_name)
);

CREATE TABLE Patient (
patient_id CHAR(36) NOT NULL,
PRIMARY KEY (patient_id)
);

CREATE TABLE Severity_level (
severity_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
severity_name VARCHAR(50) NOT NULL,
severity_risk_score TINYINT UNSIGNED NOT NULL,
PRIMARY KEY (severity_id),
UNIQUE (severity_name)
);

CREATE TABLE Infection_case (
case_id CHAR(36) NOT NULL,
user_id CHAR(36) NOT NULL,
department_id INT UNSIGNED NOT NULL,
pathogen_id INT UNSIGNED NOT NULL,
severity_id TINYINT UNSIGNED NOT NULL,
patient_id CHAR(36) NOT NULL,
case_diagnosis_date DATE NOT NULL,
case_infection_date DATE NOT NULL,
case_comments TEXT NULL,
hospital_id VARCHAR(36) NOT NULL,
PRIMARY KEY (case_id),
FOREIGN KEY (user_id) REFERENCES System_user(user_id),
FOREIGN KEY (department_id) REFERENCES Department(department_id),
FOREIGN KEY (pathogen_id) REFERENCES Pathogen(pathogen_id),
FOREIGN KEY (severity_id) REFERENCES severity_level(severity_id),
FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id),
FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
);


CREATE TABLE Manual_warning (
mwarning_id CHAR(36) NOT NULL,
user_id CHAR(36) NOT NULL,
pathogen_id INT UNSIGNED NOT NULL,
department_id INT UNSIGNED NOT NULL,
mwarning_severity_level TINYINT UNSIGNED NOT NULL,
mwarning_date DATETIME NOT NULL,
mwarning_icon TINYINT UNSIGNED NOT NULL,
mwarning_message TEXT NOT NULL,
hospital_id VARCHAR(36) NOT NULL,
PRIMARY KEY (mwarning_id),
FOREIGN KEY (user_id) REFERENCES System_user(user_id),
FOREIGN KEY (pathogen_id) REFERENCES Pathogen(pathogen_id),
FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id),
FOREIGN KEY (department_id) REFERENCES Department(department_id)
);

CREATE TABLE Threshold (
threshold_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
pathogen_id INT UNSIGNED NOT NULL,
threshold_value INT UNSIGNED NOT NULL,
threshold_time_window INT UNSIGNED NOT NULL,
PRIMARY KEY (threshold_id),
FOREIGN KEY (pathogen_id) REFERENCES Pathogen(pathogen_id)
);

CREATE TABLE Automatic_warning (
awarning_id CHAR(36) NOT NULL,
threshold_id INT UNSIGNED NOT NULL,
department_id INT UNSIGNED NULL,
awarning_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
awarning_message TEXT NOT NULL,
awarning_status ENUM('active', 'published', 'dismissed') NOT NULL,
hospital_id VARCHAR(36) NOT NULL,
PRIMARY KEY (awarning_id),
FOREIGN KEY (threshold_id) REFERENCES Threshold(threshold_id),
FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id),
FOREIGN KEY (department_id) REFERENCES Department(department_id)
);


CREATE TABLE Account_deletion_request (
request_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
user_id CHAR(36) NOT NULL,
requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
request_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
reviewed_by CHAR(36) NULL,
reviewed_at DATETIME NULL,
hospital_id VARCHAR(36) NOT NULL,
PRIMARY KEY (request_id),
FOREIGN KEY (user_id) REFERENCES System_user(user_id),
FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id),
FOREIGN KEY (reviewed_by) REFERENCES System_user(user_id)
);


CREATE TABLE Patient_ID_Mapping (
mapping_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
patient_id CHAR(36) NOT NULL,
patient_id_hmac CHAR(64) NOT NULL,
encrypted_patient_id VARBINARY(512) NOT NULL,
nonce BINARY(12) NOT NULL,
auth_tag BINARY(16) NOT NULL,
PRIMARY KEY (mapping_id),
UNIQUE KEY unique_patient_mapping (patient_id_hmac),
FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
);
