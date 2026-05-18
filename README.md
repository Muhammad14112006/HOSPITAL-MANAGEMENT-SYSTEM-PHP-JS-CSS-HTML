# HOSPITAL-MANAGEMENT-SYSTEM-PHP-JS-CSS-HTML
# 🏥 HealthCare HMS

![PHP Version](https://img.shields.io/badge/PHP-8.x-blue?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Relational_DB-orange?style=flat-square&logo=mysql)
![Vanilla JS](https://img.shields.io/badge/Vanilla_JS-ES6+-yellow?style=flat-square&logo=javascript)
![Architecture](https://img.shields.io/badge/Architecture-SPA-success?style=flat-square)

**HealthCare HMS** is a lightweight, high-performance Hospital Management System. Built entirely as a **Single Page Application (SPA)** using a monolithic PHP/MySQL backend, it delivers zero-refresh navigation and instant data filtering for all hospital staff and patients.

---

## 🚀 Core Technical Highlights
* **Zero Page Reloads:** The entire application operates from a single `index.php` file, utilizing JavaScript DOM manipulation and asynchronous API calls for a seamless app-like experience.
* **Live Search & Tabbed UI:** Every module (Patients, Appointments, Rooms, Billing) features custom tabbed interfaces and real-time data filtering (by ID, Date, Status, or Name) powered by local JSON caching.
* **Automated Data Flow:** Booking an appointment or allocating a room automatically triggers the financial engine to generate pending bills.
* **Strict Security:** Features Role-Based Access Control (RBAC), bcrypt password hashing, and PDO prepared statements to block SQL injection.

---

## 👥 Role-Based Features

### 🛡️ 1. Administrator
* **Global Control:** Full CRUD capabilities for departments and staff (Doctors/Receptionists).
* **Financial Dashboard:** Real-time analytics tracking hospital revenue, pending bills, and room occupancy.
* **Security Console:** Cryptographic override tool to force-reset passwords for any user in the system.

### 🛎️ 2. Receptionist
* **Unified Control Center:** Manage the hospital's daily operations through clean, tabbed interfaces.
* **Patient & Room Management:** Register patients, view live room statuses, and allocate beds instantly.
* **Billing Engine:** Auto-generate invoices, update payment statuses (Pending/Partial/Paid), and live-filter all hospital billing records.

### 🩺 3. Doctor
* **Clinical Dashboard:** View a filtered, live-updating queue of assigned patients.
* **Digital Prescriptions:** Search a digital medicine directory to attach specific drugs and dosages to patient records securely.
* **Patient History:** Instantly pull historical appointment and billing data for comprehensive patient care.

### 🛌 4. Patient
* **Personalized Hub:** A private, read-only portal to track personal healthcare journeys.
* **My Records:** Toggle between medical histories and financial invoices using granular search tools.
* **Prescription Tracker:** Review specific doctor instructions and medicine dosages with an interactive Month/Year filter.

---

## ⚙️ Quick Start Installation

HealthCare HMS is designed to be highly portable and deploys in seconds on any local XAMPP/WAMP server.

1. **Clone the repository** to your local server directory (e.g., `C:/xampp/htdocs/hms/`):
   ```bash
   git clone [https://github.com/yourusername/healthcare-hms.git](https://github.com/yourusername/healthcare-hms.git)
