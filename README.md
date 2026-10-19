# IMS_Project

<p align="center">
  <img src="/public/assets/img/Logo.png" alt="InfTraction Logo" width="600">
</p>


## Table of Contents
- [About](#about)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Quick Start Guide](#quick-start-guide)
- [Repository Architecture](#repository-architecture)
- [Database](#database)
- [Security & Privacy](#security--privacy)
- [Limitations and Future Work](#limitations-and-future-work)
- [Environment Variables](#environment-variables)
- [License](#license)
- [Authors](#authors)

## About 

InfTraction supports the logging and monitoring of infections within a hospital. The system aims to help healthcare staff detect unusual increases in infection cases early, so that they can investigate possible causes and take preventive measures before a larger outbreak occurs.

The system is used by three groups of users:

- *Doctors* report infection cases using a structured form (pathogen, diagnosis date, infection date, ward, patient ID, severity and comments).
- *Epidemiologists* view statistics and trends, review automatic warnings, and publish announcements with warnings and preventive measures for hospital staff.
- *Administrators* approve new user accounts, handle account removal requests, edit user information, manage wards, and edit or delete infection cases and announcements.

The reported data is stored in a relational database. The system compares the number of reported cases for each pathogen in a given time window against predefined thresholds and creates an automatic warning when a threshold is reached.
InfTraction works at the level of a single hospital, in contrast to larger surveillance systems such as HealthMap and OutbreakNow.

### Features
USER AND ACCESS

- User registration with input validation and email verification
- Login protected by a text-based CAPTCHA
- Admin approval of new accounts before users can log in
- Role-based access control (doctor, epidemiologist, chief epidemiologist and admin) with a navigation bar that only shows the pages a role can access
- Session timeout after 20 minutes of inactivity and a logout function that clears the session

INFECTION REPORTING 

- Structured case form with drop-down lists for pathogen, ward and severity, plus a free-text comments field
- Patient IDs are encrypted, and patients are matched with a keyed hash so that duplicate patient records are not created
- Case registration is handled in a database transaction
  
MONITOR AND WARNING 

- Home page with a line chart of the three most common pathogens and the three latest announcements
- Statistics page with a line chart, pie chart and bar chart, filtering by date, ward and pathogen, and a searchable table of cases
- Automatic warnings when the number of cases in a ward within a time window reaches a pathogen's threshold
- Email notification to hospital staff (and epidemiologists in other hospitals) when a case with a dangerous pathogen, for example Ebola, is reported
- Epidemiologists can prepare an automatic warning for publishing, which pre-fills the announcement form, or dismiss it

### Technologies Used

- HTML, CSS and JavaScript
- PHP, with PHPMailer for sending email through Gmail SMTP
- JavaScript
- MySQL
- Apache and MAMP (local server environment)
- Choices.js for searchable drop-down lists
- Git and GitHub
  
## Quick Start Guide

### Prerequisites & Dependencies

Before running InfTraction, the following software is required:
- MAMP
- PHP
- MySQL
- Web browser
- Git

### Installation

1. Clone the repository from GitHub.
2. Place the project folder in the MAMP `htdocs` directory.
3. Start Apache and MySQL using MAMP.
4. Create the database infection_tracking in MySQL.
5. Import the database schema into MySQL.
6. Configure the database connection in config/db.php
7. Open the project in a web browser: http://localhost:3333/IMS_Project/

First use
> The first user who registers for a hospital is given the admin role. Later users must have a verified email address and be approved by an admin before they can log in.
> Wards are added by the admin after registration, because every hospital organises its departments differently.
> Only simulated data should be used when testing the system.

## Repository Architecture
InfTraction uses a three-tier architecture:
 
- **Presentation tier:** the pages users interact with: login and registration, home, report new case, statistics, new announcement, announcements history and the admin page. All pages except the login page have a navigation bar.
- **Application tier:** Apache serves the pages and PHP handles authentication, input validation, business logic and database access. The statistics page gets its data through a `StatisticsService` class, so SQL is kept out of the page code.
- **Database tier:** a MySQL database called `infection_tracking`.

### Security & Privacy 

- **Password Hashing:** User passwords are securely hashed using PHP's `password_hash()` function before being stored in the database. This means passwords are not stored as plain text.

- **CAPTCHA:** A CAPTCHA is implemented on the login page to help prevent automated login attempts and bot activity.

- **Data Minimization:** Direct patient names and personal identity numbers are not stored. The system uses an internal patient ID together with limited demographic information such as age and sex.

- **Input Validation:** User input is validated before being processed. Structured dropdown menus are used for fields such as pathogens, wards and severity levels to reduce inconsistent data entry.

- **Role-Based Access:** Different users have different responsibilities. Doctors can report infection cases, while epidemiologists can create official announcements and warnings.
  
- **SESSIONS:** the session cookie uses the `httponly` and `samesite` settings, and sessions expire after 20 minutes of inactivity.
    
- **Email verification** uses a random token. Only a hash of the token is stored in the database.
  
-  **SQL injection:** user input is handled with prepared statements
  
-  **Patient IDs** are encrypted with AES-256-GCM. A keyed hash (HMAC-SHA-256) is used to recognise existing patients without decrypting their IDs

	

### Database

InfTraction uses MySQL as its relational database management system. The database consists of several tables connected through primary and foreign keys.
 
| Table | Description |
|---|---|
| Hospital | Hospitals and their cities |
| System_user | User accounts: name, email, phone, username, password hash and account status |
| Role | The user roles |
| Permission | The pages that can be accessed |
| Role_permission | Connects roles with permissions |
| Account_deletion_request | Requests to remove user accounts |
| Department | Wards of each hospital |
| Pathogen | Pathogen name, type, transmission, risk category and whether it is dangerous |
| Severity_level | Predefined severity levels and risk scores |
| Patient | Internal patient IDs |
| Patient_ID_Mapping | Encrypted hospital patient ID, keyed hash, nonce and authentication tag |
| Infection_case | Reported cases, connected to user, ward, patient, pathogen and severity |
| Manual_warning | Announcements written by epidemiologists |
| Automatic_warning | Automatic warnings with message, time and status (active, published or dismissed) |
| Threshold | Case count and time window for each pathogen |

The database is designed according to Third Normal Form (3NF) to reduce data redundancy and avoid duplication.

## Limitations and Future Work
 
The system currently runs locally over HTTP, and only simulated data has been used. Planned improvements are:
 
- HTTPS
- Mobile-friendly pages
- A "Forgot password" function
- Bot protection (rate limiting of login attempts and a CAPTCHA on registration)
- Trusted devices, so the CAPTCHA is not needed on every login
- A clearer statistics page and more filter functions
- A map on the other hospitals page
- Sharing anonymised data with public health authorities

### Environment Variables

The application requires the following database configuration settings(config/db.php)


DB_HOST=localhost
DB_PORT=3333
DB_NAME=infection_tracking
DB_USER=username
DB_PASSWORD=password;


### Versioning & Changelog

The project is managed using Git and GitHub. Changes to the project are tracked through Git commits and branches.


## License

Developed as part of the Information Management Systems course (1DL011) at Uppsala University for educational purposes.

## Authors

- Manuel Borbón
- Frida Engström
- Edith Nilsson
- Greta Petrenaite
- Freddy Truong
- Vaitheega Pria Gopalakrishnan
