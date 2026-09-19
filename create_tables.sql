CREATE TABLE System_user (
user_id CHAR(36) NOT NULL,
user_username VARCHAR(100) NOT NULL,
user_password_hash VARCHAR(255) NOT NULL,
user_name VARCHAR(100) NOT NULL,
user_surname VARCHAR(100) NOT NULL,
user_email VARCHAR(255) NOT NULL,
user_phone_number VARCHAR(30) NOT NULL,
user_role ENUM('doctor', 'epidemiologist') NOT NULL,
user_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (user_id),
UNIQUE (user_username),
UNIQUE (user_email),
UNIQUE (user_phone_number)
);

CREATE TABLE Department (
department_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
department_name VARCHAR(255) NOT NULL,
PRIMARY KEY (department_id),
UNIQUE (department_name)
);

CREATE TABLE Protocol (
protocol_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
protocol_transmission ENUM('contact', 'aerosol', 'droplet', 'vectorborne', 'bloodborne', 'fecal-oral') NOT NULL,
protocol_recommendation TEXT NOT NULL,
PRIMARY KEY (protocol_id)
);

CREATE TABLE Pathogen (
pathogen_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
pathogen_name VARCHAR(100) NOT NULL,
pathogen_type ENUM('bacterium', 'virus', 'fungus', 'parasite') NOT NULL,
pathogen_transmission ENUM('contact', 'aerosol', 'droplet', 'vectorborne', 'bloodborne', 'fecal-oral') NOT NULL,
pathogen_risk_category TINYINT UNSIGNED NOT NULL,
protocol_id INT UNSIGNED NOT NULL,
PRIMARY KEY (pathogen_id),
UNIQUE (pathogen_name),
FOREIGN KEY (protocol_id) REFERENCES Protocol(protocol_id)
);

CREATE TABLE Patient (
patient_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
patient_age INT NOT NULL,
patient_sex ENUM('female', 'male') NOT NULL,
PRIMARY KEY (patient_id)
);

CREATE TABLE severity_level (
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
patient_id INT UNSIGNED NOT NULL,
case_diagnosis_date DATE NOT NULL,
case_infection_date DATE NOT NULL,
case_comments TEXT NULL,
PRIMARY KEY (case_id),
FOREIGN KEY (user_id) REFERENCES System_user(user_id),
FOREIGN KEY (department_id) REFERENCES Department(department_id),
FOREIGN KEY (pathogen_id) REFERENCES Pathogen(pathogen_id),
FOREIGN KEY (severity_id) REFERENCES severity_level(severity_id),
FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
);

CREATE TABLE Manual_warning (
mwarning_id CHAR(36) NOT NULL,
user_id CHAR(36) NOT NULL,
pathogen_id INT UNSIGNED NOT NULL,
department_id INT UNSIGNED NOT NULL,
mwarning_severity_level TINYINT UNSIGNED NOT NULL,
mwarning_date DATE NOT NULL,
mwarning_icon TINYINT UNSIGNED NOT NULL,
mwarning_message TEXT NOT NULL,
PRIMARY KEY (mwarning_id),
FOREIGN KEY (user_id) REFERENCES System_user(user_id),
FOREIGN KEY (pathogen_id) REFERENCES Pathogen(pathogen_id),
FOREIGN KEY (department_id) REFERENCES Department(department_id),
FOREIGN KEY (mwarning_severity_level) REFERENCES severity_level(severity_id)
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
awarning_status ENUM('active', 'resolved') NOT NULL,
PRIMARY KEY (awarning_id),
FOREIGN KEY (threshold_id) REFERENCES Threshold(threshold_id),
FOREIGN KEY (department_id) REFERENCES Department(department_id)
);