<?php
// ============================================================
//   HealthCare HMS — PHP + MySQL Backend
//   XZApp / XAMPP Compatible — Single File Application
//   Place in: htdocs/hms/index.php
//   DB Config: edit $db_* variables below
// ============================================================

// ---------- DATABASE CONFIG ----------
function tryHosts() 
{
    try {
        // Direct connection to XAMPP default settings to eliminate loading delays
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=hms_db;charset=utf8mb4", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(['error' => 'DB Connection Failed: ' . $e->getMessage()]));
    }
}

// ---------- SESSION START ----------
session_start();

// ---------- DB CONNECTION ----------
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = tryHosts();
    }
    return $pdo;
}

// ---------- HELPERS ----------
function respond($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['hms_user']);
}

function requireLogin() {
    if (!isLoggedIn()) respond(['error' => 'Not authenticated']);
}

function role() {
    return $_SESSION['hms_user']['role'] ?? '';
}

function today() {
    return date('Y-m-d');
}

// ============================================================
//   AJAX API HANDLER
// ============================================================
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $action = $_GET['api'];
    $db = getDB();

// ---- AUTH ----
    if ($action === 'login') {
        $name = trim($_POST['name'] ?? '');
        $pass = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (!$name || !$pass) respond(['error' => 'Name and password required']);

        if ($role === 'Admin') {
            $stmt = $db->prepare("SELECT * FROM users WHERE name = ? AND role = 'Admin'");
            $stmt->execute([$name]);
            $user = $stmt->fetch();
            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['hms_user'] = ['id' => $user['id'], 'name' => $user['name'], 'role' => 'Admin', 'ref_id' => null];
                respond(['success' => true, 'user' => $_SESSION['hms_user']]);
            }
            respond(['error' => 'Invalid Admin credentials']);
            
        } elseif ($role === 'Doctor') {
            $stmt = $db->prepare("SELECT * FROM doctors WHERE name = ?");
            $stmt->execute([$name]);
            $doc = $stmt->fetch();
            if ($doc && password_verify($pass, $doc['Passwords'])) {
                $_SESSION['hms_user'] = ['id' => $doc['id'], 'name' => $doc['name'], 'role' => 'Doctor', 'ref_id' => $doc['id']];
                respond(['success' => true, 'user' => $_SESSION['hms_user']]);
            }
            respond(['error' => 'Invalid Doctor credentials']);
            
        } elseif ($role === 'Receptionist') {
            $stmt = $db->prepare("SELECT * FROM receptionists WHERE name = ?");
            $stmt->execute([$name]);
            $rec = $stmt->fetch();
            if ($rec && password_verify($pass, $rec['password'])) {
                $_SESSION['hms_user'] = ['id' => $rec['id'], 'name' => $rec['name'], 'role' => 'Receptionist', 'ref_id' => $rec['id']];
                respond(['success' => true, 'user' => $_SESSION['hms_user']]);
            }
            respond(['error' => 'Invalid Receptionist credentials']);
            
        } elseif ($role === 'Patient') {
            $stmt = $db->prepare("SELECT * FROM patients WHERE Name = ?");
            $stmt->execute([$name]);
            $pat = $stmt->fetch();
            if ($pat && password_verify($pass, $pat['password'])) {
                $_SESSION['hms_user'] = ['id' => $pat['PatientID'], 'name' => $pat['Name'], 'role' => 'Patient', 'ref_id' => $pat['PatientID']];
                respond(['success' => true, 'user' => $_SESSION['hms_user']]);
            }
            respond(['error' => 'Invalid Patient credentials']);
        } else {
            respond(['error' => 'Invalid role']);
        }
    }

    if ($action === 'logout') {
        session_destroy();
        respond(['success' => true]);
    }

    if ($action === 'session') {
        if (isLoggedIn()) respond(['logged_in' => true, 'user' => $_SESSION['hms_user']]);
        else respond(['logged_in' => false]);
    }

    // ---- DEPARTMENTS ----
    if ($action === 'get_departments') {
        requireLogin();
        $rows = $db->query("SELECT d.*, (SELECT COUNT(*) FROM doctors WHERE dept_id=d.id) AS doctor_count FROM departments d ORDER BY d.id")->fetchAll();
        respond($rows);
    }
    if ($action === 'add_department') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $name = trim($_POST['name'] ?? '');
        $loc  = trim($_POST['location'] ?? '');
        if (!$name) respond(['error' => 'Department name required']);
        // Check duplicate
        $chk = $db->prepare("SELECT id FROM departments WHERE name = ?");
        $chk->execute([$name]);
        if ($chk->fetch()) respond(['error' => 'Department already exists']);
        $stmt = $db->prepare("INSERT INTO departments (name, location) VALUES (?, ?)");
        $stmt->execute([$name, $loc]);
        respond(['success' => true, 'id' => $db->lastInsertId()]);
    }
    if ($action === 'delete_department') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM departments WHERE id = ?")->execute([$id]);
        respond(['success' => true]);
    }
    if ($action === 'update_department') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $loc  = trim($_POST['location'] ?? '');
        if (!$name) respond(['error' => 'Name required']);
        $db->prepare("UPDATE departments SET name=?, location=? WHERE id=?")->execute([$name, $loc, $id]);
        respond(['success' => true]);
    }

    // ---- DOCTORS ----
    if ($action === 'get_doctors') {
        requireLogin();
        $rows = $db->query("SELECT d.*, dep.name AS dept_name FROM doctors d LEFT JOIN departments dep ON d.dept_id=dep.id ORDER BY d.id")->fetchAll();
        respond($rows);
    }
    if ($action === 'add_doctor') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $name  = trim($_POST['name'] ?? '');
        $spec  = trim($_POST['specialization'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $sal   = (float)($_POST['salary'] ?? 0);
        $dept  = (int)($_POST['dept_id'] ?? 0);
        if (!$name) respond(['error' => 'Doctor name required']);
        if (!$dept) respond(['error' => 'Department required']);
        $stmt = $db->prepare("INSERT INTO doctors (name, specialization, phone, salary, dept_id) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $spec, $phone, $sal, $dept]);
        $docId = $db->lastInsertId();
        // Create doctor user account
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO users (name, password, role, ref_id) VALUES (?,?,?,?)")
           ->execute([$name, $hash, 'Doctor', $docId]);
        respond(['success' => true, 'id' => $docId]);
    }
    if ($action === 'delete_doctor') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM users WHERE role='Doctor' AND ref_id=?")->execute([$id]);
        $db->prepare("DELETE FROM doctors WHERE id=?")->execute([$id]);
        respond(['success' => true]);
    }
    if ($action === 'update_doctor') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $spec  = trim($_POST['specialization'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $sal   = (float)($_POST['salary'] ?? 0);
        $fee = (float)($_POST['appointment_fee'] ?? 0);
        $dept  = (int)($_POST['dept_id'] ?? 0);
        if (!$name) respond(['error' => 'Name required']);
        $db->prepare("UPDATE doctors SET name=?, specialization=?, phone=?, salary=?, dept_id=?, appointment_fee=? WHERE id=?")->execute([$name, $spec, $phone, $sal, $dept, $fee, $id]);
        $db->prepare("UPDATE users SET name=? WHERE role='Doctor' AND ref_id=?")->execute([$name, $id]);
        respond(['success' => true]);
    }

    // ---- PATIENTS ----
    if ($action === 'get_patients') {
        requireLogin();
        $rows = $db->query("SELECT * FROM patients ORDER BY PatientID")->fetchAll();
        respond($rows);
    }

    if ($action === 'add_patient') {
        requireLogin();
        if (role() !== 'Receptionist') respond(['error' => 'Only Receptionists can register new patients.']);
        
        $name   = trim($_POST['name'] ?? '');
        $age    = $_POST['age'] !== '' ? (int)$_POST['age'] : null;
        $gender = $_POST['gender'] ?? null;
        $phone  = trim($_POST['phone'] ?? '');
        $addr   = trim($_POST['address'] ?? '');
        $pass   = $_POST['password'] ?? '';
        
        if (!$name) respond(['error' => 'Patient name required']);
        if (strlen($pass) < 6 || strlen($pass) > 15) respond(['error' => 'Patient password must be between 6 and 15 characters!']);
        
        // Hash password and insert directly into patients table
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO patients (Name, Age, Gender, Phone, Address, password) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $age, $gender ?: null, $phone, $addr, $hash]);
        $pid = $db->lastInsertId();
        
        respond(['success' => true, 'id' => $pid]);
    }

    if ($action === 'delete_patient') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM users WHERE role='Patient' AND ref_id=?")->execute([$id]);
        $db->prepare("DELETE FROM patients WHERE PatientID=?")->execute([$id]);
        respond(['success' => true]);
    }
    if ($action === 'update_patient') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $id     = (int)($_POST['id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $age    = $_POST['age'] !== '' ? (int)$_POST['age'] : null;
        $gender = $_POST['gender'] ?? null;
        $phone  = trim($_POST['phone'] ?? '');
        $addr   = trim($_POST['address'] ?? '');
        if (!$name) respond(['error' => 'Name required']);
        $db->prepare("UPDATE patients SET Name=?, Age=?, Gender=?, Phone=?, Address=? WHERE PatientID=?")
           ->execute([$name, $age, $gender ?: null, $phone, $addr, $id]);
        $db->prepare("UPDATE users SET name=? WHERE role='Patient' AND ref_id=?")->execute([$name, $id]);
        respond(['success' => true]);
    }

    // ---- APPOINTMENTS ----
    if ($action === 'get_appointments') {
        requireLogin();
        $r = role();
        if ($r === 'Doctor') {
            $docId = $_SESSION['hms_user']['ref_id'];
            $rows = $db->prepare("SELECT * FROM v_appointments WHERE DoctorID=? ORDER BY EnrollmentID DESC");
            $rows->execute([$docId]);
        } elseif ($r === 'Patient') {
            $patId = $_SESSION['hms_user']['ref_id'];
            $rows = $db->prepare("SELECT * FROM v_appointments WHERE PatientID=? ORDER BY EnrollmentID DESC");
            $rows->execute([$patId]);
        } else {
            $rows = $db->query("SELECT * FROM v_appointments ORDER BY EnrollmentID DESC");
        }
        respond($rows->fetchAll());
    }
    if ($action === 'add_appointment') {
        requireLogin();
        $date   = $_POST['date'] ?? today();
        $patId  = (int)($_POST['patient_id'] ?? 0);
        $docId  = (int)($_POST['doctor_id'] ?? 0);
        $diag   = trim($_POST['diagnosis'] ?? '');
        $status = $_POST['status'] ?? 'Scheduled';
        if (!$patId) respond(['error' => 'Select a patient']);
        if (!$docId) respond(['error' => 'Select a doctor']);
        $stmt = $db->prepare("INSERT INTO appointments (Date, Diagnosis, Status, DoctorID, PatientID) VALUES (?,?,?,?,?)");
        $stmt->execute([$date, $diag, $status, $docId, $patId]);
        $enrollId = $db->lastInsertId();
        
        // Auto-Generate Bill for Appointment Fee
        $doc = $db->prepare("SELECT appointment_fee FROM doctors WHERE id=?");
        $doc->execute([$docId]);
        $fee = (float)($doc->fetchColumn() ?: 0);
        
        if ($fee > 0) {
            $db->prepare("INSERT INTO bills (TotalAmount, PaymentStatus, BillDate, EnrollmentID, PatientID) VALUES (?, 'Pending', ?, ?, ?)")->execute([$fee, today(), $enrollId, $patId]);
        }
        respond(['success' => true, 'id' => $enrollId]);    
    }
    if ($action === 'update_appointment') {
        requireLogin();
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Scheduled';
        $diag   = trim($_POST['diagnosis'] ?? '');
        $db->prepare("UPDATE appointments SET Status=?, Diagnosis=? WHERE EnrollmentID=?")
           ->execute([$status, $diag, $id]);
        respond(['success' => true]);
    }
    if ($action === 'delete_appointment') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM appointments WHERE EnrollmentID=?")->execute([$id]);
        respond(['success' => true]);
    }

    // ---- ROOMS ----
    if ($action === 'get_rooms') {
        requireLogin();
        $rows = $db->query("SELECT r.*, p.Name AS assignedName FROM rooms r LEFT JOIN patients p ON r.assignedTo=p.PatientID ORDER BY r.RoomID")->fetchAll();
        respond($rows);
    }
    if ($action === 'add_room') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $type   = $_POST['type'] ?? 'General';
        $status = $_POST['status'] ?? 'Available';
        $price  = (float)($_POST['price'] ?? 0);
        if ($price <= 0) respond(['error' => 'Enter valid price']);
        $db->prepare("INSERT INTO rooms (RoomType, RoomStatus, price) VALUES (?,?,?)")
           ->execute([$type, $status, $price]);
        respond(['success' => true, 'id' => $db->lastInsertId()]);
    }
    if ($action === 'delete_room') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM rooms WHERE RoomID=?")->execute([$id]);
        respond(['success' => true]);
    }
    if ($action === 'allocate_room') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $enrollId = (int)($_POST['enrollment_id'] ?? 0);
        $roomId   = (int)($_POST['room_id'] ?? 0);
        $nights   = (int)($_POST['nights'] ?? 1);
        if (!$enrollId) respond(['error' => 'Select an enrollment']);
        if (!$roomId)   respond(['error' => 'Select a room']);

        // Get room price & check available
        $room = $db->prepare("SELECT * FROM rooms WHERE RoomID=? AND RoomStatus='Available'");
        $room->execute([$roomId]);
        $room = $room->fetch();
        if (!$room) respond(['error' => 'Room not available']);

        // Get patient from appointment
        $appt = $db->prepare("SELECT * FROM appointments WHERE EnrollmentID=?");
        $appt->execute([$enrollId]);
        $appt = $appt->fetch();
        if (!$appt) respond(['error' => 'Enrollment not found']);

        $amount = $room['price'] * $nights;

        // Update room status
        $db->prepare("UPDATE rooms SET RoomStatus='Occupied', assignedTo=?, enrollmentId=? WHERE RoomID=?")
           ->execute([$appt['PatientID'], $enrollId, $roomId]);

        // Create bill
        $db->prepare("INSERT INTO bills (TotalAmount, PaymentStatus, BillDate, EnrollmentID, PatientID, RoomID, nights) VALUES (?,?,?,?,?,?,?)")
           ->execute([$amount, 'Pending', today(), $enrollId, $appt['PatientID'], $roomId, $nights]);

        respond(['success' => true, 'amount' => $amount, 'bill_id' => $db->lastInsertId()]);
    }
    if ($action === 'discharge_room') {
        requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE rooms SET RoomStatus='Available', assignedTo=NULL, enrollmentId=NULL WHERE RoomID=?")
           ->execute([$id]);
        respond(['success' => true]);
    }
    if ($action === 'mark_available') {
        requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE rooms SET RoomStatus='Available' WHERE RoomID=?")->execute([$id]);
        respond(['success' => true]);
    }

    // ---- BILLING ----
    if ($action === 'get_bills') {
        requireLogin();
        $r = role();
        if ($r === 'Patient') {
            $patId = $_SESSION['hms_user']['ref_id'];
            $rows = $db->prepare("SELECT * FROM v_bills WHERE PatientID=? ORDER BY BillID DESC");
            $rows->execute([$patId]);
        } else {
            $rows = $db->query("SELECT * FROM v_bills ORDER BY BillID DESC");
        }
        respond($rows->fetchAll());
    }
    if ($action === 'add_bill') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $patId    = (int)($_POST['patient_id'] ?? 0);
        $enrollId = (int)($_POST['enrollment_id'] ?? 0);
        $roomId   = (int)($_POST['room_id'] ?? 0);
        $amount   = (float)($_POST['amount'] ?? 0);
        $status   = $_POST['status'] ?? 'Pending';
        $date     = $_POST['date'] ?? today();
        if (!$patId || !$enrollId || !$roomId || $amount <= 0) respond(['error' => 'Fill all fields correctly']);
        $db->prepare("INSERT INTO bills (TotalAmount, PaymentStatus, BillDate, EnrollmentID, PatientID, RoomID) VALUES (?,?,?,?,?,?)")
           ->execute([$amount, $status, $date, $enrollId, $patId, $roomId]);
        respond(['success' => true, 'id' => $db->lastInsertId()]);
    }
    if ($action === 'update_bill_status') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Pending';
        $db->prepare("UPDATE bills SET PaymentStatus=? WHERE BillID=?")->execute([$status, $id]);
        respond(['success' => true]);
    }
    if ($action === 'delete_bill') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM bills WHERE BillID=?")->execute([$id]);
        respond(['success' => true]);
    }

    // ---- REPORT / OVERVIEW ----
    if ($action === 'get_overview') {
        requireLogin();
        $db = getDB();
        $data = [
            'departments'    => (int)$db->query("SELECT COUNT(*) FROM departments")->fetchColumn(),
            'doctors'        => (int)$db->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
            'patients'       => (int)$db->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
            'appointments'   => (int)$db->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
            'rooms'          => (int)$db->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
            'rooms_occupied' => (int)$db->query("SELECT COUNT(*) FROM rooms WHERE RoomStatus='Occupied'")->fetchColumn(),
            'pending_bills'  => (int)$db->query("SELECT COUNT(*) FROM bills WHERE PaymentStatus != 'Paid'")->fetchColumn(),
            'revenue'        => (float)$db->query("SELECT COALESCE(SUM(TotalAmount),0) FROM bills")->fetchColumn(),
            'revenue_pending'=> (float)$db->query("SELECT COALESCE(SUM(TotalAmount),0) FROM bills WHERE PaymentStatus != 'Paid'")->fetchColumn(),
        ];
        respond($data);
    }

    // ---- PRESCRIPTIONS & MEDICINES ----
    if ($action === 'search_medicines') {
        requireLogin();
        $q = trim($_POST['query'] ?? '');
        $stmt = $db->prepare("SELECT * FROM medicines WHERE name LIKE ? LIMIT 20");
        $stmt->execute(["%$q%"]);
        respond($stmt->fetchAll());
    }

    if ($action === 'add_medicine') {
        requireLogin();
        if (role() !== 'Doctor') respond(['error' => 'Access denied']);
        
        $name = trim($_POST['name'] ?? '');
        $cat  = trim($_POST['category'] ?? '');
        
        if (!$name) respond(['error' => 'Medicine name required']);
        
        $stmt = $db->prepare("INSERT INTO medicines (name, category) VALUES (?, ?)");
        $stmt->execute([$name, $cat]);
        
        respond(['success' => true, 'id' => $db->lastInsertId()]);
    }

    if ($action === 'save_prescription') {
        requireLogin();
        if (role() !== 'Doctor') respond(['error' => 'Access denied']);
        
        $enrollId = (int)($_POST['enrollment_id'] ?? 0);
        $patId    = (int)($_POST['patient_id'] ?? 0);
        $docId    = $_SESSION['hms_user']['ref_id'];
        $inst     = trim($_POST['instructions'] ?? '');
        $meds     = json_decode($_POST['medicines'] ?? '[]', true);
        
        if (!$enrollId || empty($meds)) respond(['error' => 'Enrollment and at least one medicine required']);

        try {
            $db->beginTransaction();
            // Insert main prescription
            $stmt = $db->prepare("INSERT INTO prescriptions (EnrollmentID, PatientID, DoctorID, instructions, date) VALUES (?,?,?,?,?)");
            $stmt->execute([$enrollId, $patId, $docId, $inst, today()]);
            $rxId = $db->lastInsertId();

            // Insert medicines
            $medStmt = $db->prepare("INSERT INTO prescription_items (rx_id, med_id, dosage) VALUES (?,?,?)");
            foreach ($meds as $med) {
                $medStmt->execute([$rxId, $med['med_id'], $med['dosage']]);
            }
            
            // Auto-update appointment status to Treated
            $db->prepare("UPDATE appointments SET Status='Treated' WHERE EnrollmentID=?")->execute([$enrollId]);
            
            $db->commit();
            respond(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            respond(['error' => 'Failed to save prescription: ' . $e->getMessage()]);
        }
    }

    if ($action === 'get_my_prescriptions') {
        requireLogin();
        $patId = $_SESSION['hms_user']['ref_id'];
        
        // Fetch prescriptions and join with doctors and medicines
        $stmt = $db->prepare("
            SELECT p.rx_id, p.date, p.instructions, d.name AS doctor_name, a.Diagnosis
            FROM prescriptions p
            JOIN doctors d ON p.DoctorID = d.id
            JOIN appointments a ON p.EnrollmentID = a.EnrollmentID
            WHERE p.PatientID = ? ORDER BY p.rx_id DESC
        ");
        $stmt->execute([$patId]);
        $prescriptions = $stmt->fetchAll();

        // Fetch medicines for each prescription
        foreach ($prescriptions as &$rx) {
            $medStmt = $db->prepare("
                SELECT m.name, pi.dosage 
                FROM prescription_items pi
                JOIN medicines m ON pi.med_id = m.med_id
                WHERE pi.rx_id = ?
            ");
            $medStmt->execute([$rx['rx_id']]);
            $rx['medicines'] = $medStmt->fetchAll();
        }
        respond($prescriptions);
    }
    
    // ---- DOCTOR & RECEPTIONIST REGISTRATION (ADMIN) ----
    if ($action === 'save_doctor_secure') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        
        $name = trim($_POST['name'] ?? '');
        $spec = trim($_POST['specialization'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $salary = (float)($_POST['salary'] ?? 0);
        $fee = (float)($_POST['appointment_fee'] ?? 0);
        $dept = (int)($_POST['dept_id'] ?? 0);
        $pass = $_POST['Passwords'] ?? '';
        
        if (strlen($pass) < 6 || strlen($pass) > 15) respond(['error' => 'Doctor password must be between 6 and 15 characters!']);
        
        // Ensure Global Uniqueness
        $stmt = $db->query("SELECT Passwords FROM doctors WHERE Passwords IS NOT NULL");
        while ($row = $stmt->fetch()) {
            if (password_verify($pass, $row['Passwords'])) {
                respond(['error' => 'This password is already taken by another doctor. Try a different password.']);
            }
        }
        
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO doctors (name, specialization, phone, salary, dept_id, Passwords, appointment_fee) VALUES (?,?,?,?,?,?,?)")->execute([$name, $spec, $phone, $salary, $dept, $hash, $fee]);
        respond(['success' => true]);
    }

    if ($action === 'save_receptionist') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        
        $name = trim($_POST['name'] ?? '');
        $pass = $_POST['password'] ?? '';
        $salary = (float)($_POST['salary'] ?? 0);
        
        if (strlen($pass) < 6 || strlen($pass) > 15) respond(['error' => 'Receptionist password must be between 6 and 15 characters!']);
        
        // Ensure Global Uniqueness
        $stmt = $db->query("SELECT password FROM receptionists WHERE password IS NOT NULL");
        while ($row = $stmt->fetch()) {
            if (password_verify($pass, $row['password'])) {
                respond(['error' => 'This password is already taken by another receptionist. Try a different password.']);
            }
        }
        
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO receptionists (name, password, salary) VALUES (?,?,?)")->execute([$name, $hash, $salary]);
        respond(['success' => true]);
    }

    if ($action === 'get_receptionists') {
        requireLogin();
        respond($db->query("SELECT id, name, salary FROM receptionists ORDER BY id DESC")->fetchAll());
    }
    
    if ($action === 'delete_receptionist') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM receptionists WHERE id=?")->execute([$id]);
        respond(['success' => true]);
    }

    // ---- ADMIN FORCE PASSWORD RESET ----
    if ($action === 'admin_reset_password') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied. Admin only.']);
        
        $targetRole = $_POST['target_role'] ?? '';
        $targetName = trim($_POST['target_name'] ?? '');
        $newPass    = $_POST['new_password'] ?? '';
        
        if (!$targetRole || !$targetName || !$newPass) respond(['error' => 'All fields are required.']);
        if (strlen($newPass) < 6 || strlen($newPass) > 15) respond(['error' => 'New password must be between 6 and 15 characters.']);
        
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        
        if ($targetRole === 'Doctor') {
            $stmt = $db->prepare("SELECT id FROM doctors WHERE name = ?");
            $stmt->execute([$targetName]);
            if (!$stmt->fetch()) respond(['error' => "Doctor '{$targetName}' not found in the database."]);
            
            $db->prepare("UPDATE doctors SET Passwords = ? WHERE name = ?")->execute([$hash, $targetName]);
            respond(['success' => true]);
            
        } elseif ($targetRole === 'Patient') {
            $stmt = $db->prepare("SELECT PatientID FROM patients WHERE Name = ?");
            $stmt->execute([$targetName]);
            if (!$stmt->fetch()) respond(['error' => "Patient '{$targetName}' not found in the database."]);
            
            $db->prepare("UPDATE patients SET password = ? WHERE Name = ?")->execute([$hash, $targetName]);
            respond(['success' => true]);
            
        } elseif ($targetRole === 'Receptionist') {
            $stmt = $db->prepare("SELECT id FROM receptionists WHERE name = ?");
            $stmt->execute([$targetName]);
            if (!$stmt->fetch()) respond(['error' => "Receptionist '{$targetName}' not found in the database."]);
            
            $db->prepare("UPDATE receptionists SET password = ? WHERE name = ?")->execute([$hash, $targetName]);
            respond(['success' => true]);
            
        } else {
            respond(['error' => 'Invalid role selected.']);
        }
    }
    // ---- CHANGE PASSWORD ----
    if ($action === 'change_password') {
        requireLogin();
        $old  = $_POST['old_pass'] ?? '';
        $new  = $_POST['new_pass'] ?? '';
        $uid  = $_SESSION['hms_user']['id'];
        $user = $db->prepare("SELECT password FROM users WHERE id=?");
        $user->execute([$uid]);
        $user = $user->fetch();
        if (!$user || !password_verify($old, $user['password'])) respond(['error' => 'Current password incorrect']);
        if (strlen($new) < 6 || strlen($new) > 15) respond(['error' => 'New password must be between 6 and 15 characters']);
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $uid]);
        respond(['success' => true]);
    }

    respond(['error' => 'Unknown API action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthCare HMS | Professional Medical Suite</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:    #0a4d8c;
            --primary-lt: #1a6fc4;
            --accent:     #f0a500;
            --success:    #18b47a;
            --danger:     #e63946;
            --warn:       #f4a261;
            --dark:       #0d1b2a;
            --text:       #1e2d3d;
            --muted:      #607080;
            --border:     #dde3ec;
            --bg:         #f2f5fa;
            --white:      #ffffff;
            --shadow:     0 4px 24px rgba(10,77,140,0.09);
            --radius:     12px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); line-height:1.6; scroll-behavior:smooth; }
        .page { display:none; }
        .active-page { display:block; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:10px 22px; border-radius:8px; border:none; font-family:inherit; font-size:0.88rem; font-weight:600; cursor:pointer; transition:.2s; }
        .btn-blue   { background:var(--primary); color:#fff; }
        .btn-blue:hover { background:var(--primary-lt); }
        .btn-orange { background:var(--accent); color:#fff; }
        .btn-orange:hover { filter:brightness(1.08); }
        .btn-red    { background:var(--danger); color:#fff; }
        .btn-red:hover { filter:brightness(1.1); }
        .btn-green  { background:var(--success); color:#fff; }
        .btn-sm { padding:6px 13px; font-size:0.8rem; }
        .btn-lg { padding:14px 36px; font-size:1rem; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:0.82rem; font-weight:600; color:var(--muted); margin-bottom:5px; text-transform:uppercase; letter-spacing:.5px; }
        input, select, textarea {
            width:100%; padding:11px 14px; border:1.5px solid var(--border); border-radius:8px;
            font-family:inherit; font-size:0.9rem; color:var(--text); background:#fff; transition:.2s; outline:none;
        }
        input:focus, select:focus, textarea:focus { border-color:var(--primary-lt); box-shadow:0 0 0 3px rgba(26,111,196,.12); }
        input:disabled { background:#f0f4fb; color:#aaa; }
        textarea { resize:vertical; min-height:80px; }
        .form-row { display:grid; gap:14px; }
        .form-row.col2 { grid-template-columns:1fr 1fr; }
        .form-row.col3 { grid-template-columns:1fr 1fr 1fr; }
        .card { background:var(--white); border-radius:var(--radius); box-shadow:var(--shadow); padding:26px; margin-bottom:22px; }
        .card h3 { font-size:1.05rem; font-weight:700; margin-bottom:18px; color:var(--primary); display:flex; align-items:center; gap:8px; }
        .pill { padding:3px 11px; border-radius:20px; font-size:.73rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; display:inline-block; }
        .pill-green  { background:#d4f5e8; color:#0f7a52; }
        .pill-yellow { background:#fff3cd; color:#8a6300; }
        .pill-red    { background:#fde8e8; color:#a71d2a; }
        .pill-blue   { background:#dbeafe; color:#1e40af; }
        .pill-gray   { background:#e8edf4; color:#4b5e72; }
        .tbl-wrap { overflow-x:auto; border-radius:10px; }
        table { width:100%; border-collapse:collapse; font-size:.875rem; }
        th { background:#f0f4fb; color:var(--primary); font-weight:700; padding:13px 16px; text-align:left; font-size:.78rem; text-transform:uppercase; letter-spacing:.5px; }
        td { padding:13px 16px; border-bottom:1px solid #eef1f6; color:var(--text); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f9fbff; }
        /* LANDING */
        header.main-nav { display:flex; justify-content:space-between; align-items:center; padding:14px 5%; background:#fff; box-shadow:0 2px 12px rgba(0,0,0,.07); position:sticky; top:0; z-index:1000; }
        .logo { display:flex; align-items:center; gap:10px; font-size:1.5rem; font-weight:800; color:var(--primary); }
        .logo i { color:var(--danger); }
        nav a { margin:0 14px; text-decoration:none; color:var(--text); font-weight:500; font-size:.93rem; transition:.2s; }
        nav a:hover { color:var(--primary); }
        .hero { display:flex; align-items:center; padding:60px 5%; background:linear-gradient(100deg,rgba(255,255,255,.96) 42%,rgba(240,248,255,.5)), url('https://static.vecteezy.com/system/resources/thumbnails/023/740/386/small/medicine-doctor-with-stethoscope-in-hand-on-hospital-background-medical-technology-healthcare-and-medical-concept-photo.jpg'); background-size:cover; background-position:center; min-height:88vh; }
        .hero-content { max-width:580px; }
        .hero-content h1 { font-size:3.2rem; font-weight:800; color:var(--primary); line-height:1.18; margin-bottom:18px; }
        .hero-content h1 span { color:var(--accent); }
        .hero-content p { color:var(--muted); font-size:1.05rem; margin-bottom:30px; }
        .hero-btns { display:flex; gap:14px; flex-wrap:wrap; }
        .stats-bar { background:var(--primary); color:#fff; padding:40px 5%; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; text-align:center; }
        .stat-item h2 { font-size:2.4rem; font-weight:800; color:var(--accent); }
        .stat-item p { opacity:.85; font-size:.9rem; }
        .services { padding:80px 5%; text-align:center; background:#fff; }
        .services h2 { font-size:2rem; font-weight:800; color:var(--primary); margin-bottom:8px; }
        .services p.sub { color:var(--muted); margin-bottom:40px; }
        .services-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:28px; }
        .service-card { padding:38px 28px; border-radius:14px; background:var(--bg); border:1.5px solid var(--border); transition:.3s; text-align:left; }
        .service-card:hover { transform:translateY(-8px); box-shadow:var(--shadow); }
        .service-card i { font-size:2.4rem; color:var(--primary); margin-bottom:18px; display:block; }
        .service-card h3 { font-size:1.05rem; font-weight:700; margin-bottom:8px; }
        .service-card p { color:var(--muted); font-size:.9rem; }
        .login-portals { padding:80px 5%; text-align:center; background:var(--bg); }
        .login-portals h2 { font-size:2rem; font-weight:800; color:var(--primary); margin-bottom:8px; }
        .portal-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:22px; margin-top:44px; }
        .portal-card { background:#fff; padding:38px 20px 32px; border-radius:14px; box-shadow:var(--shadow); cursor:pointer; transition:.3s; border-top:4px solid transparent; }
        .portal-card:hover { transform:translateY(-8px); border-top-color:var(--primary); }
        .portal-card i { font-size:3rem; color:var(--primary); display:block; margin-bottom:16px; }
        .portal-card h3 { font-weight:700; margin-bottom:4px; }
        .portal-card p { color:var(--muted); font-size:.85rem; }
        footer { background:var(--dark); color:#a9bece; padding:60px 5% 24px; }
        .footer-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:40px; margin-bottom:40px; }
        .footer-grid h4 { color:#fff; font-weight:700; margin-bottom:14px; }
        .footer-grid p, .footer-grid a { font-size:.88rem; color:#8fa4b5; line-height:2; text-decoration:none; display:block; }
        .footer-bottom { border-top:1px solid rgba(255,255,255,.08); padding-top:20px; text-align:center; font-size:.82rem; }
        /* MODAL */
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(10,20,40,.55); z-index:2000; justify-content:center; align-items:center; backdrop-filter:blur(3px); }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:16px; width:100%; max-width:400px; padding:36px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-box h2 { font-size:1.3rem; font-weight:800; color:var(--primary); margin-bottom:24px; }
        .alert { padding:10px 14px; border-radius:8px; margin-bottom:14px; font-size:.87rem; font-weight:600; }
        .alert-error { background:#fde8e8; color:#a71d2a; }
        .alert-success { background:#d4f5e8; color:#0f7a52; }
        /* DASHBOARD */
        .dashboard-layout { display:grid; grid-template-columns:260px 1fr; min-height:100vh; }
        aside { background:#fff; border-right:1.5px solid var(--border); padding:24px 16px; display:flex; flex-direction:column; }
        .sidebar-logo { display:flex; align-items:center; gap:9px; font-size:1.2rem; font-weight:800; color:var(--primary); margin-bottom:32px; padding-left:8px; }
        .sidebar-logo i { color:var(--danger); }
        .menu-item { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:9px; cursor:pointer; color:var(--muted); font-weight:500; font-size:.9rem; margin-bottom:4px; transition:.2s; border:none; background:none; width:100%; text-align:left; }
        .menu-item i { width:18px; text-align:center; }
        .menu-item:hover { background:var(--bg); color:var(--primary); }
        .menu-item.active { background:var(--primary); color:#fff; }
        .sidebar-spacer { flex:1; }
        .dash-main { padding:30px 34px; overflow-y:auto; background:var(--bg); }
        .dash-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; }
        .dash-header h2 { font-size:1.4rem; font-weight:800; }
        .user-badge { display:flex; align-items:center; gap:10px; background:#fff; padding:8px 16px; border-radius:30px; box-shadow:var(--shadow); }
        .user-badge i { color:var(--primary); }
        .user-badge span { font-size:.88rem; font-weight:600; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:18px; margin-bottom:26px; }
        .stat-box { padding:22px; border-radius:12px; color:#fff; position:relative; overflow:hidden; }
        .stat-box h4 { font-size:.8rem; font-weight:600; opacity:.85; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px; }
        .stat-box h2 { font-size:2.2rem; font-weight:800; }
        .stat-box i { position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:2.5rem; opacity:.15; }
        .loading { text-align:center; padding:40px; color:var(--muted); }
        .loading i { font-size:2rem; animation:spin 1s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        @media(max-width:768px){
            .dashboard-layout { grid-template-columns:1fr; }
            aside { display:none; }
            .hero-content h1 { font-size:2.2rem; }
            .form-row.col2,.form-row.col3 { grid-template-columns:1fr; }
            .dash-main { padding:18px; }
        }
    </style>
</head>
<body>

<!-- ======================== LANDING ======================== -->
<div id="landingPage" class="page active-page">
    <header class="main-nav">
        <div class="logo"><i class="fas fa-heartbeat"></i> HealthCare HMS</div>
        <nav>
            <a href="#home">Home</a>
            <a href="#services">Services</a>
            <a href="#login">Portals</a>
            <a href="#login" class="btn btn-orange" style="margin-left:8px">Login Now</a>
        </nav>
    </header>
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>All-in-One<br><span>HMS Solution</span></h1>
            <p>Smarter workflows for doctors, better care for patients. Manage your hospital with confidence using our cloud-based platform.</p>
            <div class="hero-btns">
                <a href="#login" class="btn btn-blue btn-lg">Get Started</a>
                <a href="#services" class="btn btn-orange btn-lg">Explore Features</a>
            </div>
        </div>
    </section>
    <section class="stats-bar">
        <div class="stat-item"><h2>15,000+</h2><p>Active Providers</p></div>
        <div class="stat-item"><h2>50+</h2><p>Medical Specialties</p></div>
        <div class="stat-item"><h2>24/7</h2><p>Expert Support</p></div>
        <div class="stat-item"><h2>100%</h2><p>HIPAA Compliant</p></div>
    </section>
    <section class="services" id="services">
        <h2>Our Solutions</h2>
        <p class="sub">Everything your hospital needs — in one unified platform.</p>
        <div class="services-grid">
            <div class="service-card"><i class="fas fa-file-medical"></i><h3>Smart EHR</h3><p>Advanced electronic health records with AI medical scribing.</p></div>
            <div class="service-card"><i class="fas fa-file-invoice-dollar"></i><h3>Revenue Management</h3><p>End-to-end billing and RCM services with real-time tracking.</p></div>
            <div class="service-card"><i class="fas fa-hospital-alt"></i><h3>Department Control</h3><p>Manage departments, doctors, patients and rooms in one place.</p></div>
            <div class="service-card"><i class="fas fa-laptop-medical"></i><h3>Telemedicine</h3><p>Connect with patients anywhere through secure video calls.</p></div>
        </div>
    </section>
    <section class="login-portals" id="login">
        <h2>Unified Login Portals</h2>
        <p style="color:var(--muted)">Select your role to access your dedicated dashboard</p>
        <div class="portal-grid">
            <div class="portal-card" onclick="openLogin('Admin')">
                <i class="fas fa-shield-halved"></i><h3>Admin</h3><p>Full system control</p>
            </div>
            <div class="portal-card" onclick="openLogin('Doctor')">
                <i class="fas fa-user-doctor"></i><h3>Doctor</h3><p>Patients & appointments</p>
            </div>
            <div class="portal-card" onclick="openLogin('Patient')">
                <i class="fas fa-bed-pulse"></i><h3>Patient</h3><p>My records & bookings</p>
            </div>
            <div class="portal-card" onclick="openLogin('Receptionist')">
                <i class="fas fa-bell-concierge"></i><h3>Scheduling</h3><p>Rooms & registration</p>
            </div>
        </div>
    </section>
    <footer>
        <div class="footer-grid">
            <div><h4>HealthCare HMS</h4><p>Simplifying complex workflows for better patient care across every department.</p></div>
            <div><h4>Quick Links</h4><a href="#home">Home</a><a href="#services">Services</a><a href="#login">Login</a></div>
            <div><h4>Contact</h4><p><i class="fas fa-phone"></i> +1 646 663 8030</p><p><i class="fas fa-envelope"></i> support@healthcarehms.com</p></div>
        </div>
        <div class="footer-bottom">&copy; 2026 HealthCare HMS. All rights reserved.</div>
    </footer>
</div>

<!-- ======================== LOGIN MODAL ======================== -->
<div id="loginModal" class="modal-overlay">
    <div class="modal-box">
        <h2 id="loginTitle"><i class="fas fa-lock" style="color:var(--primary)"></i> Login</h2>
        <div id="loginAlert"></div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" id="username" placeholder="Enter your full name">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="password" placeholder="admin123">
        </div>
        <button class="btn btn-blue" style="width:100%;justify-content:center;margin-bottom:10px;" onclick="handleLogin()">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
        <button class="btn btn-red" style="width:100%;justify-content:center;" onclick="closeLogin()">Cancel</button>
    </div>
</div>

<!-- ======================== DASHBOARD ======================== -->
<div id="dashboardPage" class="page">
    <div class="dashboard-layout">
        <aside>
            <div class="sidebar-logo"><i class="fas fa-heartbeat"></i> HMS ELITE</div>
            <div id="sidebarMenu"></div>
            <div class="sidebar-spacer"></div>
            <button class="btn btn-red" style="width:100%;justify-content:center;" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </aside>
        <main class="dash-main">
            <div class="dash-header">
                <h2 id="viewTitle">Overview</h2>
                <div class="user-badge">
                    <i class="fas fa-user-circle fa-lg"></i>
                    <span id="userNameDisplay">Welcome</span>
                </div>
            </div>
            <div id="mainContent"></div>
        </main>
    </div>
</div>

<script>
// ============================================================
//   GLOBAL STATE
// ============================================================
let session = { user:'', role:'', id:null, ref_id:null };

// Cached data
let cache = {
    departments:[], doctors:[], patients:[],
    appointments:[], rooms:[], bills:[], overview:{}
};

// ============================================================
//   API HELPER
// ============================================================
async function api(action, data={}, method='POST') {
    const url = `?api=${action}`;
    let opts = { method };
    if (method === 'POST') {
        const fd = new FormData();
        Object.entries(data).forEach(([k,v]) => fd.append(k, v ?? ''));
        opts.body = fd;
    }
    const res = await fetch(url, opts);
    return res.json();
}

// ============================================================
//   HELPERS
// ============================================================
function deptName(id) {
    const d = cache.departments.find(x => x.id == id);
    return d ? d.name : '—';
}
function doctorName(id) {
    const d = cache.doctors.find(x => x.id == id);
    return d ? d.name : '—';
}
function patientName(id) {
    const p = cache.patients.find(x => x.PatientID == id);
    return p ? p.Name : '—';
}
function roomNo(id) {
    const r = cache.rooms.find(x => x.RoomID == id);
    return r ? `Room ${r.RoomID} (${r.RoomType})` : '—';
}
function statusPill(s) {
    const map = {
        'Available':'pill-green','Occupied':'pill-red','Maintenance':'pill-yellow',
        'Paid':'pill-green','Pending':'pill-yellow','Partial':'pill-blue','Unpaid':'pill-red',
        'Scheduled':'pill-blue','Treated':'pill-green','Cancelled':'pill-gray','Active':'pill-green'
    };
    return `<span class="pill ${map[s]||'pill-gray'}">${s}</span>`;
}
function today() { return new Date().toISOString().split('T')[0]; }
function loading(el) { el.innerHTML = `<div class="loading"><i class="fas fa-spinner"></i><p style="margin-top:12px">Loading...</p></div>`; }

// Pre-fetch all data
async function fetchAll() {
    const [depts, docs, pats, appts, rooms, bills] = await Promise.all([
        api('get_departments','','GET'),
        api('get_doctors','','GET'),
        api('get_patients','','GET'),
        api('get_appointments','','GET'),
        api('get_rooms','','GET'),
        api('get_bills','','GET')
    ]);
    cache.departments  = depts.error ? [] : depts;
    cache.doctors      = docs.error  ? [] : docs;
    cache.patients     = pats.error  ? [] : pats;
    cache.appointments = appts.error ? [] : appts;
    cache.rooms        = rooms.error ? [] : rooms;
    cache.bills        = bills.error ? [] : bills;
}

// ============================================================
//   AUTH
// ============================================================
function openLogin(role) {
    session.role = role;
    document.getElementById('loginTitle').innerHTML =
        `<i class="fas fa-lock" style="color:var(--primary)"></i> ${role} Portal`;
    document.getElementById('loginAlert').innerHTML = '';
    document.getElementById('loginModal').classList.add('open');
}
function closeLogin() { document.getElementById('loginModal').classList.remove('open'); }

async function handleLogin() {
    const name = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value;
    const alertEl = document.getElementById('loginAlert');
    if (!name || !pass) { alertEl.innerHTML = `<div class="alert alert-error">Please enter name and password.</div>`; return; }

    const res = await api('login', { name, password: pass, role: session.role });
    if (res.error) {
        alertEl.innerHTML = `<div class="alert alert-error">${res.error}</div>`;
        return;
    }
    session.user   = res.user.name;
    session.role   = res.user.role;
    session.id     = res.user.id;
    session.ref_id = res.user.ref_id;

    document.getElementById('landingPage').classList.remove('active-page');
    document.getElementById('loginModal').classList.remove('open');
    document.getElementById('dashboardPage').classList.add('active-page');
    document.getElementById('userNameDisplay').innerText = `${session.role}: ${session.user}`;

    await fetchAll();
    renderSidebar();
    const firstTab = menuConfig[session.role][0].label;
    navigate(firstTab);
}

async function logout() {
    await api('logout');
    location.reload();
}

// ============================================================
//   SIDEBAR & NAV
// ============================================================
const menuConfig = {
    Admin: [{icon:'fa-gauge',label:'Overview'},{icon:'fa-building',label:'Departments'},{icon:'fa-user-doctor',label:'Staff Management'},{icon:'fa-user-tie',label:'Receptionists'},{icon:'fa-calendar-check',label:'Appointments'},{icon:'fa-bed',label:'Room Management'},{icon:'fa-chart-bar',label:'Full Report'},{icon:'fa-key',label:'Password Reset'}],
    Doctor:       [{icon:'fa-stethoscope',label:'My Patients'},{icon:'fa-person-injured',label:'Patient History'}],
    Patient:      [{icon:'fa-calendar-alt',label:'My Appointments'},{icon:'fa-notes-medical',label:'My Records'},{icon:'fa-pills',label:'My Prescriptions'}],
    Receptionist: [{icon:'fa-user-plus',label:'Register Patient'},{icon:'fa-calendar-check',label:'Appointments'},{icon:'fa-door-open',label:'Room Allocate'},{icon:'fa-file-invoice-dollar',label:'Billing'}]
};

function renderSidebar() {
    const items = menuConfig[session.role] || [];
    document.getElementById('sidebarMenu').innerHTML = items.map(i =>
        `<button class="menu-item" onclick="navigate('${i.label}')">
            <i class="fas ${i.icon}"></i> ${i.label}
        </button>`
    ).join('');
}

async function navigate(view) {
    document.getElementById('viewTitle').innerText = view;
    document.querySelectorAll('.menu-item').forEach(el => {
        el.classList.toggle('active', el.innerText.trim() === view);
    });
    const el = document.getElementById('mainContent');
    loading(el);
    await fetchAll(); // Refresh data
    const fn = {
        'Overview':         renderOverview,
        'Departments':      renderDepts,
        'Staff Management': renderStaff,
        'Appointments':     renderAppointments,
        'Room Management':  renderRooms,
        'Full Report':      renderReport,
        'My Patients':      renderDoctorPatients,
        'Patient History':  renderDoctorHistory,
        'My Appointments': renderMyAppointments,
        'My Records':       renderMyRecords,
        'Register Patient': renderRegisterPatient,
        'Room Allocate':    renderRoomAllocate,
        'Billing':          renderBilling,
        'My Prescriptions': renderMyPrescriptions,
        'Write Prescription': renderWritePrescription,
        'Receptionists':    renderReceptionists,
        'Password Reset':   renderPasswordReset,
    }[view];
    if (fn) fn(el);
    else el.innerHTML = `<div class="card"><h3>${view} — Coming Soon</h3></div>`;
}

// ============================================================
//   OVERVIEW
// ============================================================
async function renderOverview(el) {
    const ov = await api('get_overview','','GET');
    el.innerHTML = `
        <div class="stats-grid">
            <div class="stat-box" style="background:var(--primary)">
                <h4>Doctors</h4><h2>${ov.doctors||0}</h2><i class="fas fa-user-md"></i>
            </div>
            <div class="stat-box" style="background:var(--success)">
                <h4>Patients</h4><h2>${ov.patients||0}</h2><i class="fas fa-users"></i>
            </div>
            <div class="stat-box" style="background:var(--accent)">
                <h4>Revenue</h4><h2>₨${Number(ov.revenue||0).toLocaleString()}</h2><i class="fas fa-coins"></i>
            </div>
            <div class="stat-box" style="background:var(--danger)">
                <h4>Occupied Rooms</h4><h2>${ov.rooms_occupied||0}</h2><i class="fas fa-bed"></i>
            </div>
            <div class="stat-box" style="background:#6d28d9">
                <h4>Appointments</h4><h2>${ov.appointments||0}</h2><i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-box" style="background:var(--warn)">
                <h4>Pending Bills</h4><h2>${ov.pending_bills||0}</h2><i class="fas fa-file-invoice"></i>
            </div>
        </div>
        <div class="card"><h3><i class="fas fa-bell"></i> System Notification</h3>
            <p>Welcome to HMS Elite — Connected to MySQL database. All modules are operational.</p>
        </div>`;
}

// ============================================================
//   DEPARTMENTS
// ============================================================
function deptsTableHTML(dataToRender) {
    if (dataToRender.length === 0) return `<p style="color:var(--muted);text-align:center;padding:10px;">No departments found.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>ID</th><th>Name</th><th>Location</th><th>Doctors</th><th>Action</th></tr>
        ${dataToRender.map(d => `<tr>
            <td><span style="font-family:'DM Mono',monospace;font-weight:600">#${d.id}</span></td>
            <td><b>${d.name}</b></td>
            <td>${d.location||'—'}</td>
            <td>${d.doctor_count||0}</td>
            <td style="display:flex;gap:6px">
            <button class="btn btn-sm" style="background:var(--accent);color:#fff" onclick='openEditModal("department",${JSON.stringify(d).replace(/'/g,"\'")})'><i class="fas fa-edit"></i></button>
            <button class="btn btn-red btn-sm" onclick="removeDept(${d.id},'${d.name}')"><i class="fas fa-trash"></i></button>
        </td>
        </tr>`).join('')}
    </table></div>`;
}

function renderDepts(el) {
    el.innerHTML = `
        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Add Department</h3>
            <div id="deptAlert"></div>
            <div class="form-row col3">
                <div class="form-group" style="margin:0"><label>Department ID</label><input disabled placeholder="Auto-assigned"></div>
                <div class="form-group" style="margin:0"><label>Department Name</label><input type="text" id="deptName" placeholder="e.g. Oncology"></div>
                <div class="form-group" style="margin:0"><label>Location</label><input type="text" id="deptLocation" placeholder="e.g. Block D, Floor 1"></div>
            </div>
            <button class="btn btn-blue" style="margin-top:14px" onclick="addDept()"><i class="fas fa-plus"></i> Create Department</button>
        </div>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-building"></i> All Departments</h3>
                <div style="display:flex; gap:10px;">
                    <select id="deptSearchType" onchange="filterDepts()" style="width:140px; padding:8px;">
                        <option value="name">Search by Name</option>
                        <option value="id">Search by ID</option>
                    </select>
                    <input type="text" id="deptSearchInput" onkeyup="filterDepts()" placeholder="Search..." style="width:220px; padding:8px;">
                </div>
            </div>
            <div id="deptsTableContainer">
                ${deptsTableHTML(cache.departments)}
            </div>
        </div>`;
}

function filterDepts() {
    const searchType = document.getElementById('deptSearchType').value;
    const query = document.getElementById('deptSearchInput').value.toLowerCase();
    
    const filtered = cache.departments.filter(d => {
        if (searchType === 'name') return d.name.toLowerCase().includes(query);
        if (searchType === 'id') return String(d.id).includes(query);
        return true;
    });
    document.getElementById('deptsTableContainer').innerHTML = deptsTableHTML(filtered);
}
async function addDept() {
    const name = document.getElementById('deptName').value.trim();
    const loc  = document.getElementById('deptLocation').value.trim();
    if (!name) { document.getElementById('deptAlert').innerHTML = `<div class="alert alert-error">Enter Department Name</div>`; return; }
    const res = await api('add_department', {name, location: loc});
    if (res.error) { document.getElementById('deptAlert').innerHTML = `<div class="alert alert-error">${res.error}</div>`; return; }
    navigate('Departments');
}
async function removeDept(id, name) {
    if (!confirm(`Remove department "${name}"?`)) return;
    await api('delete_department', {id});
    navigate('Departments');
}

// ============================================================
//   STAFF (DOCTORS)
// ============================================================
// ============================================================
//   STAFF (DOCTORS)
// ============================================================
// ============================================================
//   STAFF (DOCTORS)
// ============================================================
function staffTableHTML(dataToRender) {
    if (dataToRender.length === 0) return `<p style="color:var(--muted);text-align:center;padding:10px;">No doctors found matching criteria.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>ID</th><th>Name</th><th>Specialization</th><th>Phone</th><th>Salary</th><th>Fee</th><th>Department</th><th>Action</th></tr>
        ${dataToRender.map(d => `<tr>
            <td><span style="font-family:'DM Mono',monospace;font-weight:600">#${d.id}</span></td>
            <td><b>${d.name}</b></td>
            <td>${d.specialization||'—'}</td>
            <td>${d.phone||'—'}</td>
            <td>₨${Number(d.salary||0).toLocaleString()}</td>
            <td style="color:var(--success); font-weight:bold;">₨${Number(d.appointment_fee||0).toLocaleString()}</td>
            <td>${d.dept_name||'—'}</td>
            <td style="display:flex;gap:6px">
            <button class="btn btn-sm" style="background:var(--accent);color:#fff" onclick='openEditModal("doctor",${JSON.stringify(d).replace(/'/g,"\'")})'><i class="fas fa-edit"></i></button>
            <button class="btn btn-red btn-sm" onclick="removeDoctor(${d.id},'${d.name}')"><i class="fas fa-trash"></i></button>
        </td>
        </tr>`).join('')}
    </table></div>`;
}

function renderStaff(el) {
    const deptOptions = cache.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    el.innerHTML = `
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-user-doctor"></i> Doctor Management</h3>
                <button class="btn btn-blue" onclick="addDoctorSecure()"><i class="fas fa-plus"></i> Register Doctor</button>
            </div>
            
            <div style="display:flex; gap:10px; margin-bottom:15px; background:var(--bg); padding:10px; border-radius:8px; flex-wrap:wrap;">
                <select id="staffDeptFilter" onchange="filterStaff()" style="width:180px; padding:8px;">
                    <option value="All">All Departments</option>
                    ${deptOptions}
                </select>
                <select id="staffSearchType" onchange="filterStaff()" style="width:150px; padding:8px;">
                    <option value="name">Search by Name</option>
                    <option value="id">Search by ID</option>
                </select>
                <input type="text" id="staffSearchInput" onkeyup="filterStaff()" placeholder="Search Doctors..." style="flex:1; min-width:200px; padding:8px;">
            </div>

            <div id="staffTableContainer">
                ${staffTableHTML(cache.doctors)}
            </div>
        </div>`;
}

function filterStaff() {
    const deptFilter = document.getElementById('staffDeptFilter').value;
    const searchType = document.getElementById('staffSearchType').value;
    const query = document.getElementById('staffSearchInput').value.toLowerCase();

    const filtered = cache.doctors.filter(d => {
        const matchesDept = deptFilter === 'All' || String(d.dept_id) === deptFilter;
        let matchesSearch = true;
        
        if (query) {
            if (searchType === 'name') matchesSearch = d.name.toLowerCase().includes(query);
            if (searchType === 'id') matchesSearch = String(d.id).includes(query);
        }
        return matchesDept && matchesSearch;
    });

    document.getElementById('staffTableContainer').innerHTML = staffTableHTML(filtered);
}

async function removeDoctor(id, name) {
    if (!confirm(`Remove Dr. ${name}?`)) return;
    await api('delete_doctor', {id});
    navigate('Staff Management');
}

// ============================================================
//   ADMIN — ADD NEW DOCTOR (WITH UNIQUE PASSWORD)
// ============================================================
function addDoctorSecure() {
    const depts = cache.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    document.getElementById('editModalTitle').innerHTML = '<i class="fas fa-user-doctor"></i> Register New Doctor';
    document.getElementById('editModalBody').innerHTML = `
        <div id="docAlert"></div>
        <form id="docForm">
            <input type="text" name="name" placeholder="Doctor Name (e.g., Dr. Smith)" required style="width:100%;padding:8px;margin-bottom:10px;">
            <input type="text" name="specialization" placeholder="Specialization" required style="width:100%;padding:8px;margin-bottom:10px;">
            <input type="text" name="phone" placeholder="Phone Number" required style="width:100%;padding:8px;margin-bottom:10px;">
            <input type="number" name="salary" placeholder="Salary" required style="width:100%;padding:8px;margin-bottom:10px;">
            <input type="number" name="appointment_fee" placeholder="Appointment Fee (₨)" required style="width:100%;padding:8px;margin-bottom:10px;" step="0.01">
            <select name="dept_id" required style="width:100%;padding:8px;margin-bottom:10px;">
                <option value="">Select Department...</option>
                ${depts}
            </select>
            <input type="password" name="Passwords" placeholder="Unique Password (6-15 chars)" required style="width:100%;padding:8px;margin-bottom:10px;">
        </form>
    `;
    
    // Override default submit to use secure API
    document.getElementById('editModal').classList.add('open');
    window.submitEdit = async function() {
        const f = document.getElementById('docForm');
        const payload = {
            name: f.name.value.trim(),
            specialization: f.specialization.value.trim(),
            phone: f.phone.value.trim(),
            salary: f.salary.value,
            appointment_fee: f.appointment_fee.value,
            dept_id: f.dept_id.value,
            Passwords: f.Passwords.value
        };
        const res = await api('save_doctor_secure', payload);
        
        if (res.error) {
            document.getElementById('docAlert').innerHTML = `<div class="alert alert-error">${res.error}</div>`;
        } else {
            closeEditModal();
            await fetchAll();
            navigate('Staff Management');
        }
    };
}

// ============================================================
//   ADMIN — RECEPTIONIST MANAGEMENT
// ============================================================
// ============================================================
//   ADMIN — RECEPTIONIST MANAGEMENT
// ============================================================
function receptionistsTableHTML(dataToRender) {
    if (dataToRender.length === 0) return `<p style="color:var(--muted);text-align:center;padding:10px;">No receptionists found.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>ID</th><th>Name</th><th>Salary</th><th>Action</th></tr>
        ${dataToRender.map(r => `<tr>
            <td><span style="font-family:'DM Mono',monospace;font-weight:600">#${r.id}</span></td>
            <td><b>${r.name}</b></td>
            <td>₨${Number(r.salary||0).toLocaleString()}</td>
            <td>
                <button class="btn btn-red btn-sm" onclick="removeReceptionist(${r.id}, '${r.name.replace(/'/g,"\\'")}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`).join('')}
    </table></div>`;
}

async function renderReceptionists(el) {
    cache.receptionists = await api('get_receptionists', '', 'GET'); // Store in cache for searching
    el.innerHTML = `
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
            <h3 style="margin:0;"><i class="fas fa-user-tie"></i> Receptionist Management</h3>
            <button class="btn btn-blue" onclick="addReceptionist()"><i class="fas fa-plus"></i> Register Receptionist</button>
        </div>
        
        <div style="display:flex; gap:10px; margin-bottom:15px; background:var(--bg); padding:10px; border-radius:8px;">
            <select id="recSearchType" onchange="filterReceptionists()" style="width:150px; padding:8px;">
                <option value="name">Search by Name</option>
                <option value="id">Search by ID</option>
            </select>
            <input type="text" id="recSearchInput" onkeyup="filterReceptionists()" placeholder="Search Receptionists..." style="flex:1; padding:8px;">
        </div>

        <div id="receptionistsTableContainer">
            ${receptionistsTableHTML(cache.receptionists)}
        </div>
    </div>`;
}

function filterReceptionists() {
    const searchType = document.getElementById('recSearchType').value;
    const query = document.getElementById('recSearchInput').value.toLowerCase();
    
    const filtered = cache.receptionists.filter(r => {
        if (searchType === 'name') return r.name.toLowerCase().includes(query);
        if (searchType === 'id') return String(r.id).includes(query);
        return true;
    });
    document.getElementById('receptionistsTableContainer').innerHTML = receptionistsTableHTML(filtered);
}

function addReceptionist() {
    document.getElementById('editModalTitle').innerHTML = '<i class="fas fa-user-tie"></i> Register Receptionist';
    document.getElementById('editModalBody').innerHTML = `
        <div id="recAlert"></div>
        <form id="recForm">
            <input type="text" name="name" placeholder="Full Name" required style="width:100%;padding:8px;margin-bottom:10px;">
            <input type="number" name="salary" placeholder="Salary Amount" required style="width:100%;padding:8px;margin-bottom:10px;">
            <input type="password" name="password" placeholder="Unique Password (6-15 chars)" required style="width:100%;padding:8px;margin-bottom:10px;">
        </form>
    `;
    
    document.getElementById('editModal').classList.add('open');
    window.submitEdit = async function() {
        const f = document.getElementById('recForm');
        const payload = {
            name: f.name.value.trim(),
            salary: f.salary.value,
            password: f.password.value
        };
        const res = await api('save_receptionist', payload);
        
        if (res.error) {
            document.getElementById('recAlert').innerHTML = `<div class="alert alert-error">${res.error}</div>`;
        } else {
            closeEditModal();
            navigate('Receptionists');
        }
    };
}

async function removeReceptionist(id, name) {
    if (!confirm(`Are you sure you want to delete Receptionist "${name}"? They will no longer be able to log in.`)) return;
    await api('delete_receptionist', {id});
    navigate('Receptionists');
}

// ============================================================
//   PATIENTS
// ============================================================
function patientFormHTML() {
    return `
        <div id="patAlert"></div>
        <div class="form-row col2">
            <div class="form-group" style="margin:0"><label>Patient ID</label><input disabled placeholder="Auto-assigned"></div>
            <div class="form-group" style="margin:0"><label>Full Name</label><input type="text" id="patName" placeholder="Full Name"></div>
        </div>
        <div class="form-row col3" style="margin-top:10px">
            <div class="form-group" style="margin:0"><label>Age</label><input type="number" id="patAge" placeholder="Age" min="0" max="150"></div>
            <div class="form-group" style="margin:0">
                <label>Gender</label>
                <select id="patGender">
                    <option value="">— Select —</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>
            </div>
            <div class="form-group" style="margin:0"><label>Phone</label><input type="text" id="patPhone" placeholder="0300-0000000"></div>
        </div>
        <div class="form-row col2" style="margin-top:10px">
            <div class="form-group" style="margin:0"><label>Address</label><input type="text" id="patAddr" placeholder="Street, City"></div>
            <div class="form-group" style="margin:0"><label>Password</label><input type="password" id="patPass" placeholder="6-15 chars"></div>
        </div>`;
}
function patientsTableHTML(dataToRender = cache.patients) {
    if (!dataToRender.length) return `<p style="color:var(--muted); padding:10px;">No patients found matching criteria.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Phone</th><th>Address</th><th>Action</th></tr>
        ${dataToRender.map(p => `<tr>
            <td><span style="font-family:'DM Mono',monospace">#${p.PatientID}</span></td>
            <td><b>${p.Name}</b></td>
            <td>${p.Age||'—'}</td>
            <td>${p.Gender==='M'?'Male':p.Gender==='F'?'Female':p.Gender==='O'?'Other':'—'}</td>
            <td>${p.Phone||'—'}</td>
            <td>${p.Address||'—'}</td>
            <td style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:var(--accent);color:#fff" onclick='openEditModal("patient",${JSON.stringify(p).replace(/'/g,"\\'")})'><i class="fas fa-edit"></i></button>
                <button class="btn btn-red btn-sm" onclick="removePatient(${p.PatientID},'${p.Name.replace(/'/g,"\\'")}')"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('')}
    </table></div>`;
}
function renderRegisterPatient(el) {
    el.innerHTML = `
        <div class="card" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-blue" id="btnViewRegister" onclick="togglePatientView('register')"><i class="fas fa-user-plus"></i> Register New Patient</button>
            <button class="btn" style="background:var(--bg); color:var(--text);" id="btnViewList" onclick="togglePatientView('list')"><i class="fas fa-users"></i> Registered Patients</button>
        </div>

        <div id="sectionRegisterPatientForm" class="card">
            <h3><i class="fas fa-user-plus"></i> Patient Registration</h3>
            ${patientFormHTML()}
            <button class="btn btn-blue" style="margin-top:10px" onclick="submitPatient()"><i class="fas fa-check"></i> Register Patient</button>
        </div>

        <div id="sectionRegisteredPatientsList" class="card" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-users"></i> Registered Patients</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select id="filterPatientGender" onchange="filterPatients()" style="width:130px; padding:8px;">
                        <option value="All">All Genders</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="O">Other</option>
                    </select>
                    <select id="filterPatientSearchType" onchange="filterPatients()" style="width:140px; padding:8px;">
                        <option value="name">Search by Name</option>
                        <option value="id">Search by ID</option>
                    </select>
                    <input type="text" id="filterPatientInput" onkeyup="filterPatients()" placeholder="Search..." style="width:180px; padding:8px;">
                </div>
            </div>
            <div id="patientsTableContainer">
                ${patientsTableHTML(cache.patients)}
            </div>
        </div>`;
}
async function submitPatient() {
    const name   = document.getElementById('patName').value.trim();
    const age    = document.getElementById('patAge').value;
    const gender = document.getElementById('patGender').value;
    const phone  = document.getElementById('patPhone').value.trim();
    const addr   = document.getElementById('patAddr').value.trim();
    const pass   = document.getElementById('patPass').value;

    if (!name) { document.getElementById('patAlert').innerHTML=`<div class="alert alert-error">Enter Patient Name</div>`; return; }
    if (pass.length < 6 || pass.length > 15) { document.getElementById('patAlert').innerHTML=`<div class="alert alert-error">Password must be 6-15 chars</div>`; return; }

    const res = await api('add_patient', {name, age, gender, phone, address:addr, password:pass});
    if (res.error) { document.getElementById('patAlert').innerHTML=`<div class="alert alert-error">${res.error}</div>`; return; }
    
    alert(`Patient "${name}" registered successfully!`);
    navigate('Register Patient');
}
async function removePatient(id, name) {
    if (!confirm(`Remove patient "${name}"?`)) return;
    await api('delete_patient', {id});
    navigate('Register Patient');
}

// ============================================================
//   APPOINTMENTS
// ============================================================
function apptFormHTML(patientFixed=false) {
    const patOpts = patientFixed && session.ref_id
        ? `<option value="${session.ref_id}">${session.user}</option>`
        : cache.patients.map(p => `<option value="${p.PatientID}">${p.Name}</option>`).join('');
    return `
        <div id="apptAlert"></div>
        <div class="form-row col2">
            <div class="form-group" style="margin:0"><label>Enrollment ID</label><input disabled placeholder="Auto-assigned"></div>
            <div class="form-group" style="margin:0"><label>Date</label><input type="date" id="apptDate" value="${today()}"></div>
        </div>

        <div class="form-row col3" style="margin-top:10px">
            <div class="form-group" style="margin:0">
                <label>Patient</label>
                <select id="apptPatient" ${patientFixed?'disabled':''}>
                    <option value="">— Select Patient —</option>${patOpts}
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label>Doctor</label>
                <select id="apptDoctor" onchange="updateApptFee()">
                    <option value="">— Select Doctor —</option>
                    ${cache.doctors.map(d => `<option value="${d.id}">${d.name} (${d.dept_name||'—'})</option>`).join('')}
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label>Doctor Fee (₨)</label>
                <input type="text" id="apptFeeDisplay" disabled placeholder="Select a doctor...">
            </div>
        </div>

        <div class="form-group" style="margin-top:10px"><label>Diagnosis / Notes</label><textarea id="apptDiag" placeholder="Initial notes or reason for visit..."></textarea></div>
        <div class="form-group">
            <label>Status</label>
            <select id="apptStatus">
                <option value="Scheduled">Scheduled</option>
                <option value="Pending">Pending</option>
                <option value="Treated">Treated</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>`;
}
function apptTableHTML(rows, showActions=true) {
    if (!rows.length) return `<p style="color:var(--muted)">No appointments yet.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>EnrollmentID</th><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Status</th>${showActions?'<th>Action</th>':''}</tr>
        ${rows.map(a => `<tr>
            <td><span style="font-family:'DM Mono',monospace">#${a.EnrollmentID}</span></td>
            <td>${a.Date||'—'}</td>
            <td>${a.PatientName||patientName(a.PatientID)}</td>
            <td>${a.DoctorName||doctorName(a.DoctorID)}</td>
            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${a.Diagnosis||'—'}</td>
            <td>${statusPill(a.Status)}</td>
            ${showActions?`<td>
                <button class="btn btn-sm" style="background:#6d28d9;color:#fff;margin-right:4px" onclick="updateApptStatus(${a.EnrollmentID},'${a.Status}','${(a.Diagnosis||'').replace(/'/g,"\\'")}')"><i class="fas fa-edit"></i></button>
                <button class="btn btn-red btn-sm" onclick="deleteAppt(${a.EnrollmentID})"><i class="fas fa-trash"></i></button>
            </td>`:''}
        </tr>`).join('')}
    </table></div>`;
}
function renderAppointments(el) {
    const isAdmin = session.role === 'Admin';
    
    el.innerHTML = `
        ${isAdmin ? '' : `
        <div class="card" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-blue" id="btnViewBookAppt" onclick="toggleApptView('book')"><i class="fas fa-calendar-plus"></i> Book Appointment</button>
            <button class="btn" style="background:var(--bg); color:var(--text);" id="btnViewAllAppts" onclick="toggleApptView('list')"><i class="fas fa-list-alt"></i> All Appointments</button>
        </div>
        
        <div id="sectionBookAppt" class="card">
            <h3><i class="fas fa-calendar-plus"></i> Book Appointment</h3>
            ${apptFormHTML()}
            <button class="btn btn-blue" style="margin-top:10px" onclick="submitAppointment()"><i class="fas fa-plus"></i> Book Appointment</button>
        </div>
        `}

        <div id="sectionAllAppts" class="card" ${isAdmin ? '' : 'style="display:none;"'}>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-list-alt"></i> All Appointments</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select id="adminApptStatus" onchange="filterAdminAppointments()" style="width:130px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Pending">Pending</option>
                        <option value="Treated">Treated</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <select id="adminApptSearchType" onchange="filterAdminAppointments()" style="width:140px; padding:8px;">
                        <option value="name">Patient Name</option>
                        <option value="id">Enrollment ID</option>
                    </select>
                    <input type="text" id="adminApptSearch" onkeyup="filterAdminAppointments()" placeholder="Search..." style="width:200px; padding:8px;">
                </div>
            </div>
            <div id="adminApptsTableContainer">
                ${apptTableHTML(cache.appointments, !isAdmin)}
            </div>
        </div>`;
}

function filterAdminAppointments() {
    const status = document.getElementById('adminApptStatus').value;
    const searchType = document.getElementById('adminApptSearchType').value;
    const query = document.getElementById('adminApptSearch').value.toLowerCase();
    const isAdmin = session.role === 'Admin';

    const filtered = cache.appointments.filter(a => {
        const matchesStatus = status === 'All' || a.Status === status;
        let matchesSearch = true;
        
        if (query) {
            if (searchType === 'id') {
                matchesSearch = String(a.EnrollmentID).includes(query);
            } else {
                const pName = (a.PatientName || patientName(a.PatientID)).toLowerCase();
                matchesSearch = pName.includes(query);
            }
        }
        return matchesStatus && matchesSearch;
    });

    document.getElementById('adminApptsTableContainer').innerHTML = apptTableHTML(filtered, !isAdmin);
}
function renderMyAppointments(el) {
    const mine = cache.appointments.filter(a => a.PatientID == session.ref_id);
    el.innerHTML = `
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-calendar-alt"></i> My Appointments</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="date" id="patApptDateFilter" onchange="filterPatientAppointments()" style="width:140px; padding:8px;" title="Filter by Date">
                    <select id="patApptStatusFilter" onchange="filterPatientAppointments()" style="width:140px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Pending">Pending</option>
                        <option value="Treated">Treated</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <input type="text" id="patApptSearchInput" onkeyup="filterPatientAppointments()" placeholder="Search Enroll ID..." style="width:180px; padding:8px;">
                </div>
            </div>
            <div id="patientApptsTableContainer">
                ${apptTableHTML(mine, false)}
            </div>
        </div>`;
}
async function submitAppointment(patFixed=false) {
    const date   = document.getElementById('apptDate').value;
    const patId  = patFixed ? session.ref_id : document.getElementById('apptPatient').value;
    const docId  = document.getElementById('apptDoctor').value;
    const diag   = document.getElementById('apptDiag').value.trim();
    const status = document.getElementById('apptStatus').value;
    if (!patId) { document.getElementById('apptAlert').innerHTML=`<div class="alert alert-error">Select a Patient</div>`; return; }
    if (!docId) { document.getElementById('apptAlert').innerHTML=`<div class="alert alert-error">Select a Doctor</div>`; return; }
    const res = await api('add_appointment', {date, patient_id:patId, doctor_id:docId, diagnosis:diag, status});
    if (res.error) { document.getElementById('apptAlert').innerHTML=`<div class="alert alert-error">${res.error}</div>`; return; }
    alert("Appointment booked!");
    navigate(session.role==='Patient' ? 'Book Appointment' : 'Appointments');
}
async function updateApptStatus(id, curStatus, curDiag) {
    const s = prompt("Update status: Scheduled / Pending / Treated / Cancelled", curStatus);
    if (s && ['Scheduled','Pending','Treated','Cancelled'].includes(s)) {
        const diag = prompt("Update diagnosis/notes:", curDiag) ?? curDiag;
        await api('update_appointment', {id, status:s, diagnosis:diag});
        navigate('Appointments');
    }
}
async function deleteAppt(id) {
    if (!confirm(`Delete appointment #${id}?`)) return;
    await api('delete_appointment', {id});
    navigate('Appointments');
}

// ============================================================
//   ROOMS — ADMIN
// ============================================================
function roomsTableHTML(dataToRender) {
    if (dataToRender.length === 0) return `<p style="color:var(--muted);text-align:center;padding:10px;">No rooms found.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>Room ID</th><th>Type</th><th>Price/Night</th><th>Status</th><th>Assigned Patient</th><th>Action</th></tr>
        ${dataToRender.map(r => `<tr>
            <td><span style="font-family:'DM Mono',monospace;font-weight:bold;">#${r.RoomID}</span></td>
            <td>${r.RoomType}</td>
            <td>₨${Number(r.price||0).toLocaleString()}</td>
            <td>${statusPill(r.RoomStatus)}</td>
            <td>${r.assignedName||'—'}</td>
            <td>
                ${r.RoomStatus==='Occupied'
                    ? `<button class="btn btn-sm btn-green" onclick="dischargeRoom(${r.RoomID})"><i class="fas fa-sign-out-alt"></i> Discharge</button>`
                    : r.RoomStatus==='Maintenance'
                        ? `<button class="btn btn-sm btn-blue" onclick="markAvailable(${r.RoomID})">Mark Available</button>`
                        : `<button class="btn btn-red btn-sm" onclick="deleteRoom(${r.RoomID})"><i class="fas fa-trash"></i></button>`}
            </td>
        </tr>`).join('')}
    </table></div>`;
}

function renderRooms(el) {
    el.innerHTML = `
        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Add Room</h3>
            <div id="roomAlert"></div>
            <div class="form-row col3">
                <div class="form-group" style="margin:0"><label>Room ID</label><input disabled placeholder="Auto-assigned"></div>
                <div class="form-group" style="margin:0">
                    <label>Room Type</label>
                    <select id="newRoomType">
                        <option>General</option><option>Private</option><option>ICU</option><option>Semi-Private</option><option>Emergency</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Status</label>
                    <select id="newRoomStatus"><option value="Available">Available</option><option value="Maintenance">Maintenance</option></select>
                </div>
            </div>
            <div class="form-group" style="margin-top:10px"><label>Price per Night (₨)</label><input type="number" id="newRoomPrice" placeholder="e.g. 5000"></div>
            <button class="btn btn-blue" onclick="addRoom()"><i class="fas fa-plus"></i> Add Room</button>
        </div>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-table"></i> Room Status</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select id="filterRoomStatus" onchange="filterRooms()" style="width:130px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                    <select id="filterRoomType" onchange="filterRooms()" style="width:130px; padding:8px;">
                        <option value="All">All Types</option>
                        <option value="General">General</option>
                        <option value="Private">Private</option>
                        <option value="ICU">ICU</option>
                        <option value="Semi-Private">Semi-Private</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                    <input type="text" id="searchRoomId" onkeyup="filterRooms()" placeholder="Search Room ID..." style="width:160px; padding:8px;">
                </div>
            </div>
            <div id="roomsTableContainer">
                ${roomsTableHTML(cache.rooms)}
            </div>
        </div>`;
}

function filterRooms() {
    const status = document.getElementById('filterRoomStatus').value;
    const type = document.getElementById('filterRoomType').value;
    const query = document.getElementById('searchRoomId').value.trim();

    const filtered = cache.rooms.filter(r => {
        const matchesStatus = status === 'All' || r.RoomStatus === status;
        const matchesType = type === 'All' || r.RoomType === type;
        const matchesId = query === '' || String(r.RoomID).includes(query);
        
        return matchesStatus && matchesType && matchesId;
    });

    document.getElementById('roomsTableContainer').innerHTML = roomsTableHTML(filtered);
}
async function addRoom() {
    const type   = document.getElementById('newRoomType').value;
    const status = document.getElementById('newRoomStatus').value;
    const price  = document.getElementById('newRoomPrice').value;
    if (!price || price < 1) { document.getElementById('roomAlert').innerHTML=`<div class="alert alert-error">Enter valid price</div>`; return; }
    await api('add_room', {type, status, price});
    navigate('Room Management');
}
async function deleteRoom(id) {
    if (!confirm(`Delete Room #${id}?`)) return;
    await api('delete_room', {id});
    navigate('Room Management');
}
async function dischargeRoom(id) {
    if (!confirm(`Discharge patient from Room #${id}?`)) return;
    await api('discharge_room', {id});
    navigate(session.role==='Receptionist' ? 'Room Allocate' : 'Room Management');
}
async function markAvailable(id) {
    await api('mark_available', {id});
    navigate(session.role==='Receptionist' ? 'Room Allocate' : 'Room Management');
}

//==================================================
//   ROOM ALLOCATION — RECEPTIONIST
//==================================================
function renderRoomAllocate(el) {
    const availableRooms = cache.rooms.filter(r => r.RoomStatus === 'Available');
    el.innerHTML = `
        <div class="card" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-blue" id="btnViewAllocate" onclick="toggleRoomAllocateView('allocate')"><i class="fas fa-door-open"></i> Allocate Room to Patients</button>
            <button class="btn" style="background:var(--bg); color:var(--text);" id="btnViewRoomStatus" onclick="toggleRoomAllocateView('list')"><i class="fas fa-table"></i> Room Status</button>
        </div>

        <div id="sectionAllocateRoom" class="card">
            <h3><i class="fas fa-door-open"></i> Allocate Room to Patient</h3>
            <div id="allocAlert"></div>
            <div class="form-row col3">
                <div class="form-group" style="margin:0">
                    <label>Enrollment (Appointment)</label>
                    <select id="roomEnrollment">
                        <option value="">— Select Enrollment —</option>
                        ${cache.appointments.filter(a=>a.Status!=='Cancelled').map(a =>
                            `<option value="${a.EnrollmentID}">[#${a.EnrollmentID}] ${a.PatientName||patientName(a.PatientID)} — ${a.DoctorName||doctorName(a.DoctorID)}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Select Room</label>
                    <select id="roomSelect">
                        ${availableRooms.length===0
                            ? '<option disabled>No rooms available</option>'
                            : availableRooms.map(r => `<option value="${r.RoomID}">Room #${r.RoomID} — ${r.RoomType} (₨${r.price}/night)</option>`).join('')}
                    </select>
                </div>
                <div class="form-group" style="margin:0"><label>Nights</label><input type="number" id="roomNights" placeholder="1" min="1" value="1"></div>
            </div>
            <button class="btn btn-blue" style="margin-top:6px" onclick="allocateRoom()"><i class="fas fa-check-circle"></i> Allocate Room</button>
        </div>

        <div id="sectionRoomStatusList" class="card" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-table"></i> Room Status</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select id="filterRoomStatus" onchange="filterRooms()" style="width:130px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                    <select id="filterRoomType" onchange="filterRooms()" style="width:130px; padding:8px;">
                        <option value="All">All Types</option>
                        <option value="General">General</option>
                        <option value="Private">Private</option>
                        <option value="ICU">ICU</option>
                        <option value="Semi-Private">Semi-Private</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                    <input type="text" id="searchRoomId" onkeyup="filterRooms()" placeholder="Search Room ID..." style="width:160px; padding:8px;">
                </div>
            </div>
            <div id="roomsTableContainer">
                ${roomsTableHTML(cache.rooms)}
            </div>
        </div>`;
}
async function allocateRoom() {
    const enrollmentId = document.getElementById('roomEnrollment').value;
    const roomId       = document.getElementById('roomSelect').value;
    const nights       = document.getElementById('roomNights').value || 1;
    if (!enrollmentId) { document.getElementById('allocAlert').innerHTML=`<div class="alert alert-error">Select an enrollment</div>`; return; }
    if (!roomId)       { document.getElementById('allocAlert').innerHTML=`<div class="alert alert-error">Select a room</div>`; return; }
    const res = await api('allocate_room', {enrollment_id:enrollmentId, room_id:roomId, nights});
    if (res.error) { document.getElementById('allocAlert').innerHTML=`<div class="alert alert-error">${res.error}</div>`; return; }
    alert(`Room #${roomId} allocated.\nBill generated: ₨${Number(res.amount).toLocaleString()} for ${nights} night(s).`);
    navigate('Room Allocate');
}

// ============================================================
//   BILLING
// ============================================================
function renderBilling(el) {
    el.innerHTML = `
        <div class="card" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-blue" id="btnViewCreateBill" onclick="toggleBillingView('create')"><i class="fas fa-plus-circle"></i> Create New Bill</button>
            <button class="btn" style="background:var(--bg); color:var(--text);" id="btnViewAllBills" onclick="toggleBillingView('list')"><i class="fas fa-file-invoice-dollar"></i> All Billing Records</button>
        </div>

        <div id="sectionCreateBill" class="card">
            <h3><i class="fas fa-plus-circle"></i> Create New Bill</h3>
            <div id="billAlert"></div>
            <div class="form-row col2">
                <div class="form-group" style="margin:0"><label>Bill ID</label><input disabled placeholder="Auto-assigned"></div>
                <div class="form-group" style="margin:0"><label>Bill Date</label><input type="date" id="billDate" value="${today()}"></div>
            </div>
            <div class="form-row col3" style="margin-top:10px">
                <div class="form-group" style="margin:0">
                    <label>Patient</label>
                    <select id="billPatient">
                        <option value="">— Select Patient —</option>
                        ${cache.patients.map(p=>`<option value="${p.PatientID}">${p.Name} (#${p.PatientID})</option>`).join('')}
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Enrollment (Appointment)</label>
                    <select id="billEnrollment">
                        <option value="">— Select Enrollment —</option>
                        ${cache.appointments.map(a=>`<option value="${a.EnrollmentID}">[#${a.EnrollmentID}] ${a.PatientName||patientName(a.PatientID)} — ${a.DoctorName||doctorName(a.DoctorID)}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Room</label>
                    <select id="billRoom">
                        <option value="">— Select Room —</option>
                        ${cache.rooms.map(r=>`<option value="${r.RoomID}">Room #${r.RoomID} — ${r.RoomType}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="form-row col2" style="margin-top:10px">
                <div class="form-group" style="margin:0"><label>Total Amount (₨)</label><input type="number" id="billAmount" placeholder="e.g. 15000" min="0"></div>
                <div class="form-group" style="margin:0">
                    <label>Payment Status</label>
                    <select id="billStatus"><option value="Pending">Pending</option><option value="Paid">Paid</option><option value="Partial">Partial</option></select>
                </div>
            </div>
            <button class="btn btn-blue" style="margin-top:6px" onclick="submitBill()"><i class="fas fa-plus"></i> Generate Bill</button>
        </div>

        <div id="sectionAllBills" class="card" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-file-invoice-dollar"></i> All Billing Records</h3>
                <div style="display:flex; gap:10px;">
                    <select id="billStatusFilter" onchange="filterBills()" style="width:160px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Partial">Partial</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <input type="text" id="billSearchInput" onkeyup="filterBills()" placeholder="Search Name or ID..." style="width:220px; padding:8px;">
                </div>
            </div>
            <div id="billingTableContainer">
                ${billsTableHTML()}
            </div>
        </div>`;
}
function billsTableHTML(billsToRender = cache.bills) {
    if (!billsToRender.length) return `<p style="color:var(--muted)">No bills found matching your criteria.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>Bill ID</th><th>Patient</th><th>Enrollment</th><th>Room</th><th>Amount</th><th>Bill Date</th><th>Payment Status</th><th>Actions</th></tr>
        ${billsToRender.map(b => `<tr>
            <td><span style="font-family:'DM Mono',monospace;font-weight:700">#${b.BillID}</span></td>
            <td><b>${b.PatientName||patientName(b.PatientID)}</b><br><span style="color:var(--muted);font-size:.78rem">ID: #${b.PatientID}</span></td>
            <td><span style="font-family:'DM Mono',monospace">#${b.EnrollmentID}</span></td>
            <td>${b.RoomType ? `Room ${b.RoomID} (${b.RoomType})` : roomNo(b.RoomID)}</td>
            <td><b style="color:var(--primary);font-size:1rem">₨${Number(b.TotalAmount).toLocaleString()}</b></td>
            <td>${b.BillDate}</td>
            <td>${statusPill(b.PaymentStatus)}</td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                ${b.PaymentStatus==='Pending'  ? `<button class="btn btn-green btn-sm" onclick="changeBillStatus(${b.BillID},'Paid')"><i class="fas fa-check"></i> Paid</button>` : ''}
                ${b.PaymentStatus==='Pending'  ? `<button class="btn btn-sm" style="background:#6d28d9;color:#fff" onclick="changeBillStatus(${b.BillID},'Partial')"><i class="fas fa-adjust"></i> Partial</button>` : ''}
                ${b.PaymentStatus==='Partial'  ? `<button class="btn btn-green btn-sm" onclick="changeBillStatus(${b.BillID},'Paid')"><i class="fas fa-check"></i> Full Pay</button>` : ''}
                <button class="btn btn-red btn-sm" onclick="deleteBill(${b.BillID})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('')}
    </table></div>`;
}
async function submitBill() {
    const patId    = document.getElementById('billPatient').value;
    const enrollId = document.getElementById('billEnrollment').value;
    const roomId   = document.getElementById('billRoom').value;
    const amount   = document.getElementById('billAmount').value;
    const status   = document.getElementById('billStatus').value;
    const date     = document.getElementById('billDate').value;
    if (!patId||!enrollId||!roomId||!amount||amount<=0) {
        document.getElementById('billAlert').innerHTML=`<div class="alert alert-error">Fill all fields correctly</div>`; return;
    }
    const res = await api('add_bill', {patient_id:patId, enrollment_id:enrollId, room_id:roomId, amount, status, date});
    if (res.error) { document.getElementById('billAlert').innerHTML=`<div class="alert alert-error">${res.error}</div>`; return; }
    alert("Bill generated successfully!");
    navigate('Billing');
}
async function changeBillStatus(id, newStatus) {
    await api('update_bill_status', {id, status:newStatus});
    navigate('Billing');
}
async function deleteBill(id) {
    if (!confirm(`Delete Bill #${id}?`)) return;
    await api('delete_bill', {id});
    navigate('Billing');
}

// ============================================================
//   DOCTOR — MY PATIENTS (UPDATED WITH SEARCH)
// ============================================================
function doctorPatientsTableHTML(appointmentsToRender) {
    if (appointmentsToRender.length === 0) return `<p style="color:var(--muted)">No patients found matching your criteria.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>Enroll ID</th><th>Date</th><th>Patient</th><th>Diagnosis</th><th>Status</th><th>Actions</th></tr>
        ${appointmentsToRender.map(a => `<tr>
            <td>#${a.EnrollmentID}</td>
            <td>${a.Date||'—'}</td>
            <td><b>${a.PatientName||patientName(a.PatientID)}</b></td>
            <td>${a.Diagnosis||'Pending'}</td>
            <td>${statusPill(a.Status)}</td>
            <td style="display:flex;gap:6px;">
                <button class="btn btn-sm" style="background:#6d28d9;color:#fff" onclick="updateApptStatus(${a.EnrollmentID},'${a.Status}','${(a.Diagnosis||'').replace(/'/g,"\\'")}')"><i class="fas fa-edit"></i> Edit Status</button>
                <button class="btn btn-blue btn-sm" onclick="startPrescription(${a.EnrollmentID}, ${a.PatientID})"><i class="fas fa-prescription"></i> Prescribe</button>
            </td>
        </tr>`).join('')}
    </table></div>`;
}

function renderDoctorPatients(el) {
    const myWork = cache.appointments.filter(a => a.DoctorID == session.ref_id);
    el.innerHTML = `
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-stethoscope"></i> My Assigned Patients</h3>
                <div style="display:flex; gap:10px;">
                    <select id="docPatientStatusFilter" onchange="filterDoctorPatients()" style="width:160px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Pending">Pending</option>
                        <option value="Treated">Treated</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <input type="text" id="docPatientSearchInput" onkeyup="filterDoctorPatients()" placeholder="Search Name or ID..." style="width:220px; padding:8px;">
                </div>
            </div>
            <div id="doctorPatientsTableContainer">
                ${doctorPatientsTableHTML(myWork)}
            </div>
        </div>`;
}

// ============================================================
//   DOCTOR — WRITE PRESCRIPTION
// ============================================================
let currentRx = { enrollmentId: null, patientId: null, meds: [] };

function startPrescription(enrollId, patId) {
    currentRx = { enrollmentId: enrollId, patientId: patId, meds: [] };
    navigate('Write Prescription');
}

function renderWritePrescription(el) {
    el.innerHTML = `
        <div class="card">
            <h3><i class="fas fa-prescription-bottle-medical"></i> Write Prescription (Enrollment #${currentRx.enrollmentId})</h3>
            <div id="rxAlert"></div>
            
            <div class="form-group" style="position:relative;">
                <label>Search Medicines</label>
                <div style="display:flex;gap:10px;">
                    <input type="text" id="medSearch" placeholder="Type medicine name to search..." onkeyup="searchMeds(event)">
                    <button class="btn btn-blue" onclick="searchMeds(null, true)"><i class="fas fa-search"></i></button>
                </div>
                <div id="medDropdown" style="display:none;position:absolute;top:70px;left:0;width:100%;background:#fff;border:1px solid var(--border);border-radius:8px;box-shadow:var(--shadow);z-index:100;max-height:200px;overflow-y:auto;"></div>
            </div>

            <div class="form-group" style="margin-top:20px;">
                <label>Selected Medicines</label>
                <div class="tbl-wrap">
                    <table id="rxMedsTable">
                        <tr><th>Medicine</th><th>Dosage Instructions</th><th>Action</th></tr>
                        <tr><td colspan="3" style="color:#aaa;text-align:center">No medicines added yet.</td></tr>
                    </table>
                </div>
            </div>

            <div class="form-group" style="margin-top:20px;">
                <label>General Doctor Instructions</label>
                <textarea id="rxInstructions" placeholder="Dietary advice, rest instructions, etc..."></textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button class="btn btn-green" style="flex:1;justify-content:center" onclick="savePrescription()"><i class="fas fa-save"></i> Save Prescription</button>
                <button class="btn btn-red" style="flex:1;justify-content:center" onclick="navigate('My Patients')"><i class="fas fa-times"></i> Cancel</button>
            </div>
        </div>`;
}

async function searchMeds(e, force = false) {
    const q = document.getElementById('medSearch').value;
    if (!force && q.length < 2) {
        document.getElementById('medDropdown').style.display = 'none';
        return;
    }
    const res = await api('search_medicines', { query: q });
    const drop = document.getElementById('medDropdown');
    drop.innerHTML = '';
    
    if (res.length === 0) {
        drop.innerHTML = `<div style="padding:10px;color:var(--muted)">No medicines found matching "${q}".</div>`;
    } else {
        res.forEach(m => {
            const div = document.createElement('div');
            div.style.cssText = "padding:10px;border-bottom:1px solid #eee;cursor:pointer;";
            div.innerHTML = `<b>${m.name}</b> <span style="font-size:12px;color:gray">(${m.category})</span>`;
            div.onclick = () => addMedToRx(m);
            drop.appendChild(div);
        });
    }
    
    // Always append an "Add New" button at the bottom of the dropdown
    const addNewBtn = document.createElement('div');
    addNewBtn.style.cssText = "padding:10px;background:#f0f4fb;cursor:pointer;color:var(--primary);font-weight:bold;text-align:center;border-bottom-left-radius:8px;border-bottom-right-radius:8px;";
    addNewBtn.innerHTML = `<i class="fas fa-plus-circle"></i> Create New Medicine`;
    addNewBtn.onclick = () => promptNewMedicine(q);
    drop.appendChild(addNewBtn);
    
    drop.style.display = 'block';
}

async function promptNewMedicine(suggestedName) {
    // Hide the dropdown while prompting
    document.getElementById('medDropdown').style.display = 'none';
    document.getElementById('medSearch').value = '';

    const name = prompt("Register New Medicine\n\nEnter Medicine Name:", suggestedName);
    if (!name) return;

    const category = prompt(`Enter Category for ${name}\n(e.g., Tablet, Syrup, Injection, Cream):`, "Tablet");
    if (!category) return;

    const res = await api('add_medicine', { name: name.trim(), category: category.trim() });

    if (res.error) {
        alert("Error: " + res.error);
    } else {
        // Automatically add it to the prescription table so the doctor doesn't have to search for it again!
        addMedToRx({ med_id: res.id, name: name.trim(), category: category.trim() });
    }
}

function addMedToRx(med) {
    if (currentRx.meds.find(m => m.med_id === med.med_id)) return alert("Medicine already added");
    const dosage = prompt(`Enter dosage for ${med.name} (e.g., 1 pill twice a day):`);
    if (dosage !== null) {
        currentRx.meds.push({ med_id: med.med_id, name: med.name, dosage: dosage });
        updateRxTable();
    }
    document.getElementById('medDropdown').style.display = 'none';
    document.getElementById('medSearch').value = '';
}

function removeMedFromRx(id) {
    currentRx.meds = currentRx.meds.filter(m => m.med_id !== id);
    updateRxTable();
}

function updateRxTable() {
    const table = document.getElementById('rxMedsTable');
    if (currentRx.meds.length === 0) {
        table.innerHTML = `<tr><th>Medicine</th><th>Dosage Instructions</th><th>Action</th></tr><tr><td colspan="3" style="color:#aaa;text-align:center">No medicines added yet.</td></tr>`;
        return;
    }
    table.innerHTML = `<tr><th>Medicine</th><th>Dosage Instructions</th><th>Action</th></tr>` + 
        currentRx.meds.map(m => `<tr>
            <td><b>${m.name}</b></td>
            <td>${m.dosage}</td>
            <td><button class="btn btn-red btn-sm" onclick="removeMedFromRx(${m.med_id})"><i class="fas fa-trash"></i></button></td>
        </tr>`).join('');
}

async function savePrescription() {
    if (currentRx.meds.length === 0) { document.getElementById('rxAlert').innerHTML = `<div class="alert alert-error">Please add at least one medicine.</div>`; return; }
    
    const inst = document.getElementById('rxInstructions').value;
    const res = await api('save_prescription', {
        enrollment_id: currentRx.enrollmentId,
        patient_id: currentRx.patientId,
        instructions: inst,
        medicines: JSON.stringify(currentRx.meds)
    });
    
    if (res.error) { document.getElementById('rxAlert').innerHTML = `<div class="alert alert-error">${res.error}</div>`; return; }
    alert("Prescription saved successfully!");
    navigate('My Patients');
}

// ============================================================
//   PATIENT — MY PRESCRIPTIONS (UPDATED WITH MONTH FILTER)
// ============================================================
function prescriptionsListHTML(rxs) {
    if (rxs.length === 0) return `<p style="color:var(--muted)">No prescriptions found matching your criteria.</p>`;
    return rxs.map(rx => `
        <div style="border:1.5px solid var(--border);border-radius:8px;padding:16px;margin-bottom:16px;background:#fff">
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:10px;margin-bottom:10px;">
                <div><b style="color:var(--primary)">Dr. ${rx.doctor_name}</b><br><span style="font-size:12px;color:var(--muted)">Diagnosis: ${rx.Diagnosis || 'N/A'}</span></div>
                <div style="text-align:right"><span class="pill pill-blue">${rx.date}</span><br><span style="font-size:12px;color:var(--muted)">Rx #${rx.rx_id}</span></div>
            </div>
            <div class="tbl-wrap" style="margin-bottom:10px;">
                <table style="font-size:0.85rem">
                    <tr style="background:#f9fbff"><th style="padding:8px">Medicine</th><th style="padding:8px">Dosage</th></tr>
                    ${rx.medicines.map(m => `<tr><td style="padding:8px"><b>${m.name}</b></td><td style="padding:8px">${m.dosage}</td></tr>`).join('')}
                </table>
            </div>
            ${rx.instructions ? `<div style="background:#f0f4fb;padding:10px;border-radius:6px;font-size:0.85rem"><b>Instructions:</b> ${rx.instructions}</div>` : ''}
        </div>
    `).join('');
}

async function renderMyPrescriptions(el) {
    const res = await api('get_my_prescriptions', '', 'GET');
    cache.prescriptions = res.error ? [] : res; // Save to cache for fast filtering!
    
    el.innerHTML = `
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-pills"></i> My Prescriptions</h3>
                <div style="display:flex; gap:10px;">
                    <input type="month" id="filterRxMonth" onchange="filterMyPrescriptions()" style="width:180px; padding:8px;" title="Filter by Month">
                </div>
            </div>
            <div id="myPrescriptionsContainer">
                ${prescriptionsListHTML(cache.prescriptions)}
            </div>
        </div>`;
}

// ============================================================
//   DOCTOR — PATIENT HISTORY
// ============================================================
// ============================================================
//   DOCTOR — PATIENT HISTORY (UPDATED WITH SEARCH)
// ============================================================
function doctorHistoryCardsHTML(patIds, myAppts) {
    if (!patIds.length) return `<div class="card"><p style="color:var(--muted)">No patient history found.</p></div>`;
    return patIds.map(pid => {
        const p = cache.patients.find(x => x.PatientID == pid);
        const history = myAppts.filter(a => a.PatientID == pid);
        const patBills = cache.bills.filter(b => b.PatientID == pid);
        return `
        <div class="card" style="margin-top:15px">
            <h3><i class="fas fa-user-injured"></i> ${p?p.Name:'Unknown Patient'} ${p?`<span class="pill pill-blue">${p.Gender==='M'?'Male':'Female'}, ${p.Age} yrs</span>`:''}</h3>
            ${p?`<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-bottom:16px;font-size:.88rem;">
                <div><span style="color:var(--muted)">Phone:</span> ${p.Phone||'—'}</div>
                <div><span style="color:var(--muted)">Address:</span> ${p.Address||'—'}</div>
                <div><span style="color:var(--muted)">Patient ID:</span> #${p.PatientID}</div>
            </div>`:''}
            <h4 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:10px">Appointment History</h4>
            <div class="tbl-wrap"><table>
                <tr><th>Enroll ID</th><th>Date</th><th>Diagnosis</th><th>Status</th></tr>
                ${history.map(a=>`<tr>
                    <td>#${a.EnrollmentID}</td><td>${a.Date||'—'}</td>
                    <td>${a.Diagnosis||'—'}</td><td>${statusPill(a.Status)}</td>
                </tr>`).join('')}
            </table></div>
            ${patBills.length>0?`
            <h4 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin:14px 0 10px">Billing History</h4>
            <div class="tbl-wrap"><table>
                <tr><th>Bill ID</th><th>Room</th><th>Amount</th><th>Date</th><th>Status</th></tr>
                ${patBills.map(b=>`<tr>
                    <td>#${b.BillID}</td><td>${roomNo(b.RoomID)}</td>
                    <td>₨${Number(b.TotalAmount).toLocaleString()}</td>
                    <td>${b.BillDate}</td><td>${statusPill(b.PaymentStatus)}</td>
                </tr>`).join('')}
            </table></div>`:''}
        </div>`;
    }).join('');
}

function renderDoctorHistory(el) {
    const myAppts = cache.appointments.filter(a => a.DoctorID == session.ref_id);
    const patIds  = [...new Set(myAppts.map(a => a.PatientID))];
    
    el.innerHTML = `
        <div class="card" style="margin-bottom: 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0;"><i class="fas fa-notes-medical"></i> Patient History Search</h3>
                <input type="text" id="docHistorySearchInput" onkeyup="filterDoctorHistory()" placeholder="Search Name or ID..." style="width:280px; padding:8px;">
            </div>
        </div>
        <div id="doctorHistoryContainer">
            ${doctorHistoryCardsHTML(patIds, myAppts)}
        </div>`;
}

// ============================================================
//   PATIENT — MY RECORDS (UPDATED)
// ============================================================
function patientRecordsTableHTML(data) {
    if (!data.length) return `<p style="color:var(--muted); padding:10px;">No medical records found matching your criteria.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Status</th></tr>
        ${data.map(a=>`<tr>
            <td>${a.Date||'—'}</td>
            <td>${a.DoctorName||doctorName(a.DoctorID)}</td>
            <td style="max-width:300px;">${a.Diagnosis||'Pending'}</td>
            <td>${statusPill(a.Status)}</td>
        </tr>`).join('')}
    </table></div>`;
}

function patientBillsTableHTML(data) {
    if (!data.length) return `<p style="color:var(--muted); padding:10px;">No billing records found matching your criteria.</p>`;
    return `<div class="tbl-wrap"><table>
        <tr><th>Bill ID</th><th>Room</th><th>Amount</th><th>Date</th><th>Status</th></tr>
        ${data.map(b=>`<tr>
            <td><span style="font-family:'DM Mono',monospace;font-weight:700">#${b.BillID}</span></td>
            <td>${b.RoomType?`Room ${b.RoomID} (${b.RoomType})`:roomNo(b.RoomID)}</td>
            <td><b style="color:var(--primary)">₨${Number(b.TotalAmount).toLocaleString()}</b></td>
            <td>${b.BillDate}</td>
            <td>${statusPill(b.PaymentStatus)}</td>
        </tr>`).join('')}
    </table></div>`;
}

function renderMyRecords(el) {
    const mine = cache.appointments.filter(a => a.PatientID == session.ref_id);
    const myBills = cache.bills.filter(b => b.PatientID == session.ref_id);
    
    el.innerHTML = `
        <div class="card" style="margin-bottom:15px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0;"><i class="fas fa-folder-open"></i> Choose the Type of Record</h3>
                <select id="recordTypeSelector" onchange="toggleRecordView()" style="width:250px; padding:10px; font-weight:bold; color:var(--primary);">
                    <option value="medical">My Medical Records</option>
                    <option value="bills">My Bills</option>
                </select>
            </div>
        </div>

        <div id="sectionMedicalRecords" class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-notes-medical"></i> My Medical Records</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="date" id="filterMedDate" onchange="filterMyMedicalRecords()" style="width:140px; padding:8px;" title="Search by Date">
                    <select id="filterMedStatus" onchange="filterMyMedicalRecords()" style="width:130px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Pending">Pending</option>
                        <option value="Treated">Treated</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <select id="filterMedSearchType" onchange="filterMyMedicalRecords()" style="width:130px; padding:8px;">
                        <option value="doctor">Doctor Name</option>
                        <option value="diagnosis">Diagnosis</option>
                    </select>
                    <input type="text" id="filterMedSearch" onkeyup="filterMyMedicalRecords()" placeholder="Search..." style="width:160px; padding:8px;">
                </div>
            </div>
            <div id="myMedicalTableContainer">
                ${patientRecordsTableHTML(mine)}
            </div>
        </div>

        <div id="sectionBills" class="card" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-file-invoice-dollar"></i> My Bills</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="date" id="filterBillDate" onchange="filterMyBills()" style="width:140px; padding:8px;" title="Search by Date">
                    <select id="filterBillStatus" onchange="filterMyBills()" style="width:140px; padding:8px;">
                        <option value="All">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Partial">Partial</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <input type="text" id="filterBillId" onkeyup="filterMyBills()" placeholder="Search Bill ID..." style="width:160px; padding:8px;">
                </div>
            </div>
            <div id="myBillsTableContainer">
                ${patientBillsTableHTML(myBills)}
            </div>
        </div>
    `;
}

// ============================================================
//   FULL REPORT (Admin)
// ============================================================
async function renderReport(el) {
    const ov = await api('get_overview','','GET');
    const paid   = cache.bills.filter(b=>b.PaymentStatus==='Paid').reduce((s,b)=>s+Number(b.TotalAmount),0);
    const unpaid = cache.bills.filter(b=>b.PaymentStatus!=='Paid').reduce((s,b)=>s+Number(b.TotalAmount),0);
    el.innerHTML = `
        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> Hospital Summary Report</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:16px;">
                <div style="background:var(--bg);padding:16px;border-radius:10px;">
                    <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Total Departments</div>
                    <div style="font-size:1.8rem;font-weight:800;color:var(--primary)">${ov.departments||0}</div>
                </div>
                <div style="background:var(--bg);padding:16px;border-radius:10px;">
                    <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Total Doctors</div>
                    <div style="font-size:1.8rem;font-weight:800;color:var(--primary)">${ov.doctors||0}</div>
                </div>
                <div style="background:var(--bg);padding:16px;border-radius:10px;">
                    <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Registered Patients</div>
                    <div style="font-size:1.8rem;font-weight:800;color:var(--success)">${ov.patients||0}</div>
                </div>
                <div style="background:var(--bg);padding:16px;border-radius:10px;">
                    <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Appointments</div>
                    <div style="font-size:1.8rem;font-weight:800;color:#6d28d9">${ov.appointments||0}</div>
                </div>
                <div style="background:var(--bg);padding:16px;border-radius:10px;">
                    <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Total Rooms</div>
                    <div style="font-size:1.8rem;font-weight:800;color:var(--warn)">${ov.rooms||0}</div>
                </div>
                <div style="background:var(--bg);padding:16px;border-radius:10px;">
                    <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Revenue Collected</div>
                    <div style="font-size:1.8rem;font-weight:800;color:var(--accent)">₨${paid.toLocaleString()}</div>
                </div>
                <div style="background:#d4f5e8;padding:16px;border-radius:10px;">
                    <div style="color:#0f7a52;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Collected</div>
                    <div style="font-size:1.8rem;font-weight:800;color:#0f7a52">₨${paid.toLocaleString()}</div>
                </div>
                <div style="background:#fde8e8;padding:16px;border-radius:10px;">
                    <div style="color:#a71d2a;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Pending</div>
                    <div style="font-size:1.8rem;font-weight:800;color:#a71d2a">₨${unpaid.toLocaleString()}</div>
                </div>
            </div>
            <button class="btn btn-blue" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
        </div>`;
}

// ============================================================
//   ADMIN — PASSWORD RESET TOOL
// ============================================================
function renderPasswordReset(el) {
    el.innerHTML = `
        <div class="card" style="max-width:500px;">
            <h3><i class="fas fa-key"></i> Force Password Reset</h3>
            <p style="color:var(--muted);font-size:0.9rem;margin-bottom:15px;">Override and change a forgotten password for any staff member or patient.</p>
            <div id="resetAlert"></div>
            <form id="resetForm" onsubmit="event.preventDefault(); submitPasswordReset();">
                <div class="form-group">
                    <label>Select Role</label>
                    <select id="resetRole" required>
                        <option value="">— Select Role —</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Patient">Patient</option>
                        <option value="Receptionist">Receptionist</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Exact Full Name</label>
                    <input type="text" id="resetName" placeholder="e.g. Dr. Naseer Ul Din" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="resetPass" placeholder="6-15 characters" required minlength="6" maxlength="15">
                </div>
                <button type="submit" class="btn btn-red" style="width:100%;justify-content:center;margin-top:10px;padding:12px;">
                    <i class="fas fa-exclamation-triangle"></i> Force Change Password
                </button>
            </form>
        </div>
    `;
}

async function submitPasswordReset() {
    const role = document.getElementById('resetRole').value;
    const name = document.getElementById('resetName').value.trim();
    const pass = document.getElementById('resetPass').value;
    const alertEl = document.getElementById('resetAlert');
    
    if (pass.length < 6 || pass.length > 15) {
        alertEl.innerHTML = `<div class="alert alert-error">Password must be 6-15 characters.</div>`;
        return;
    }

    const res = await api('admin_reset_password', { target_role: role, target_name: name, new_password: pass });
    
    if (res.error) {
        alertEl.innerHTML = `<div class="alert alert-error">${res.error}</div>`;
    } else {
        alertEl.innerHTML = `<div class="alert alert-success"><i class="fas fa-check"></i> Password for ${name} changed successfully!</div>`;
        document.getElementById('resetForm').reset();
    }
}
// ============================================================
//   EDIT MODAL
// ============================================================
let editState = {};

function openEditModal(type, data) {
    editState = { type, data };
    document.getElementById('editModal').classList.add('open');
    const titleMap = { doctor:'Edit Doctor', patient:'Edit Patient', department:'Edit Department' };
    document.getElementById('editModalTitle').innerHTML = `<i class="fas fa-edit" style="color:var(--primary)"></i> ${titleMap[type]}`;

    let body = '';
    if (type === 'doctor') {
        body = `
        <div id="editAlert"></div>
        <div class="form-group"><label>Full Name</label><input id="e_name" value="${data.name||''}"></div>
        <div class="form-group"><label>Specialization</label><input id="e_spec" value="${data.specialization||''}"></div>
        <div class="form-group"><label>Phone</label><input id="e_phone" value="${data.phone||''}"></div>
        <div class="form-group"><label>Salary (₨)</label><input type="number" id="e_salary" value="${data.salary||0}"></div>
        <div class="form-group"><label>Appt Fee (₨)</label><input type="number" id="e_fee" value="${data.appointment_fee||0}" step="0.01"></div>
        <div class="form-group"><label>Department</label>
            <select id="e_dept">
                ${cache.departments.map(d=>`<option value="${d.id}" ${d.id==data.dept_id?'selected':''}>${d.name}</option>`).join('')}
            </select>
        </div>`;
    } else if (type === 'patient') {
        const gMap = {'M':'Male','F':'Female','O':'Other'};
        body = `
        <div id="editAlert"></div>
        <div class="form-group"><label>Full Name</label><input id="e_name" value="${data.Name||''}"></div>
        <div class="form-group"><label>Age</label><input type="number" id="e_age" value="${data.Age||''}"></div>
        <div class="form-group"><label>Gender</label>
            <select id="e_gender">
                <option value="M" ${data.Gender==='M'?'selected':''}>Male</option>
                <option value="F" ${data.Gender==='F'?'selected':''}>Female</option>
                <option value="O" ${data.Gender==='O'?'selected':''}>Other</option>
            </select>
        </div>
        <div class="form-group"><label>Phone</label><input id="e_phone" value="${data.Phone||''}"></div>
        <div class="form-group"><label>Address</label><input id="e_address" value="${data.Address||''}"></div>`;
    } else if (type === 'department') {
        body = `
        <div id="editAlert"></div>
        <div class="form-group"><label>Department Name</label><input id="e_name" value="${data.name||''}"></div>
        <div class="form-group"><label>Location</label><input id="e_location" value="${data.location||''}"></div>`;
    }
    document.getElementById('editModalBody').innerHTML = body;
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    editState = {};
}

async function submitEdit() {
    const { type, data } = editState;
    let payload = {}, action = '';

    if (type === 'doctor') {
        action = 'update_doctor';
        payload = {
            id: data.id,
            name: document.getElementById('e_name').value.trim(),
            specialization: document.getElementById('e_spec').value.trim(),
            phone: document.getElementById('e_phone').value.trim(),
            salary: document.getElementById('e_salary').value,
            appointment_fee: document.getElementById('e_fee').value,
            dept_id: document.getElementById('e_dept').value,
        };
        if (!payload.name) { document.getElementById('editAlert').innerHTML=`<div class="alert alert-error">Name required</div>`; return; }
    } else if (type === 'patient') {
        action = 'update_patient';
        payload = {
            id: data.PatientID,
            name: document.getElementById('e_name').value.trim(),
            age: document.getElementById('e_age').value,
            gender: document.getElementById('e_gender').value,
            phone: document.getElementById('e_phone').value.trim(),
            address: document.getElementById('e_address').value.trim(),
        };
        if (!payload.name) { document.getElementById('editAlert').innerHTML=`<div class="alert alert-error">Name required</div>`; return; }
    } else if (type === 'department') {
        action = 'update_department';
        payload = {
            id: data.id,
            name: document.getElementById('e_name').value.trim(),
            location: document.getElementById('e_location').value.trim(),
        };
        if (!payload.name) { document.getElementById('editAlert').innerHTML=`<div class="alert alert-error">Name required</div>`; return; }
    }

    const res = await api(action, payload);
    if (res.error) { document.getElementById('editAlert').innerHTML=`<div class="alert alert-error">${res.error}</div>`; return; }
    closeEditModal();
    const navMap = { doctor:'Staff Management', patient:'Register Patient', department:'Departments' };
    navigate(navMap[type]);
}

// ============================================================
//   AUTO-LOGIN CHECK (restore session if already logged in)
// ============================================================
(async () => {
    const s = await api('session','','GET');
    if (s.logged_in) {
        session.user   = s.user.name;
        session.role   = s.user.role;
        session.id     = s.user.id;
        session.ref_id = s.user.ref_id;
        document.getElementById('landingPage').classList.remove('active-page');
        document.getElementById('dashboardPage').classList.add('active-page');
        document.getElementById('userNameDisplay').innerText = `${session.role}: ${session.user}`;
        await fetchAll();
        renderSidebar();
        // Automatically load the first tab available for this specific role
        const firstTab = menuConfig[session.role][0].label;
        navigate(firstTab);
    }
})();

// ============================================================
//   APPOINTMENT FEE AUTOFILL
// ============================================================
function updateApptFee() {
    const docId = document.getElementById('apptDoctor').value;
    const doc = cache.doctors.find(d => d.id == docId);
    const feeInput = document.getElementById('apptFeeDisplay');
    
    if (doc && doc.appointment_fee > 0) {
        feeInput.value = '₨ ' + Number(doc.appointment_fee).toLocaleString();
        feeInput.style.fontWeight = 'bold';
        feeInput.style.color = 'var(--primary)';
    } else {
        feeInput.value = 'No Fee / N/A';
        feeInput.style.color = '';
    }
}

// ============================================================
//   BILLING SEARCH & FILTER
// ============================================================
function filterBills() {
    const query = document.getElementById('billSearchInput').value.toLowerCase();
    const status = document.getElementById('billStatusFilter').value;

    const filteredBills = cache.bills.filter(b => {
        // Get the patient name and ID
        const patName = (b.PatientName || patientName(b.PatientID)).toLowerCase();
        const patId = String(b.PatientID);
        
        // Check if it matches the search box
        const matchesSearch = patName.includes(query) || patId.includes(query);
        
        // Check if it matches the dropdown status
        const matchesStatus = status === 'All' || b.PaymentStatus === status;
        
        return matchesSearch && matchesStatus;
    });

    // Instantly inject the filtered table back into the UI
    document.getElementById('billingTableContainer').innerHTML = billsTableHTML(filteredBills);
}

// ============================================================
//   DOCTOR SEARCH & FILTER LOGIC
// ============================================================
function filterDoctorPatients() {
    const query = document.getElementById('docPatientSearchInput').value.toLowerCase();
    const status = document.getElementById('docPatientStatusFilter').value;
    const myWork = cache.appointments.filter(a => a.DoctorID == session.ref_id);

    const filteredWork = myWork.filter(a => {
        const patName = (a.PatientName || patientName(a.PatientID)).toLowerCase();
        const patId = String(a.PatientID);
        
        const matchesSearch = patName.includes(query) || patId.includes(query);
        const matchesStatus = status === 'All' || a.Status === status;
        
        return matchesSearch && matchesStatus;
    });

    document.getElementById('doctorPatientsTableContainer').innerHTML = doctorPatientsTableHTML(filteredWork);
}

function filterDoctorHistory() {
    const query = document.getElementById('docHistorySearchInput').value.toLowerCase();
    const myAppts = cache.appointments.filter(a => a.DoctorID == session.ref_id);
    const allPatIds  = [...new Set(myAppts.map(a => a.PatientID))];

    const filteredPatIds = allPatIds.filter(pid => {
        const p = cache.patients.find(x => x.PatientID == pid);
        const patName = p ? p.Name.toLowerCase() : '';
        const patIdStr = String(pid);
        return patName.includes(query) || patIdStr.includes(query);
    });

    document.getElementById('doctorHistoryContainer').innerHTML = doctorHistoryCardsHTML(filteredPatIds, myAppts);
}

// ============================================================
//   PATIENT SEARCH & FILTER LOGIC
// ============================================================
function filterPatientAppointments() {
    const status = document.getElementById('patApptStatusFilter').value;
    const query = document.getElementById('patApptSearchInput').value.trim();
    const dateFilter = document.getElementById('patApptDateFilter').value; 
    
    // Grab only this specific patient's appointments
    const mine = cache.appointments.filter(a => a.PatientID == session.ref_id);

    const filtered = mine.filter(a => {
        const matchesStatus = status === 'All' || a.Status === status;
        const matchesSearch = query === '' || String(a.EnrollmentID).includes(query);
        const matchesDate = dateFilter === '' || a.Date === dateFilter;
        
        return matchesStatus && matchesSearch && matchesDate;
    });

    // Instantly inject the filtered table back into the UI (passing 'false' to hide admin action buttons)
    document.getElementById('patientApptsTableContainer').innerHTML = apptTableHTML(filtered, false);
}

// ============================================================
//   PATIENT MY RECORDS LOGIC
// ============================================================
function toggleRecordView() {
    const type = document.getElementById('recordTypeSelector').value;
    if (type === 'medical') {
        document.getElementById('sectionMedicalRecords').style.display = 'block';
        document.getElementById('sectionBills').style.display = 'none';
    } else {
        document.getElementById('sectionMedicalRecords').style.display = 'none';
        document.getElementById('sectionBills').style.display = 'block';
    }
}

function filterMyMedicalRecords() {
    const date = document.getElementById('filterMedDate').value;
    const status = document.getElementById('filterMedStatus').value;
    const searchType = document.getElementById('filterMedSearchType').value;
    const query = document.getElementById('filterMedSearch').value.toLowerCase();
    
    const mine = cache.appointments.filter(a => a.PatientID == session.ref_id);

    const filtered = mine.filter(a => {
        const matchesDate = date === '' || a.Date === date;
        const matchesStatus = status === 'All' || a.Status === status;
        let matchesSearch = true;
        
        if (query) {
            if (searchType === 'doctor') {
                const docName = (a.DoctorName || doctorName(a.DoctorID)).toLowerCase();
                matchesSearch = docName.includes(query);
            } else {
                const diag = (a.Diagnosis || '').toLowerCase();
                matchesSearch = diag.includes(query);
            }
        }
        return matchesDate && matchesStatus && matchesSearch;
    });

    document.getElementById('myMedicalTableContainer').innerHTML = patientRecordsTableHTML(filtered);
}

function filterMyBills() {
    const date = document.getElementById('filterBillDate').value;
    const status = document.getElementById('filterBillStatus').value;
    const query = document.getElementById('filterBillId').value.trim();
    
    const myBills = cache.bills.filter(b => b.PatientID == session.ref_id);

    const filtered = myBills.filter(b => {
        const matchesDate = date === '' || b.BillDate === date;
        const matchesStatus = status === 'All' || b.PaymentStatus === status;
        const matchesSearch = query === '' || String(b.BillID).includes(query);
        
        return matchesDate && matchesStatus && matchesSearch;
    });

    document.getElementById('myBillsTableContainer').innerHTML = patientBillsTableHTML(filtered);
}

function filterMyPrescriptions() {
    // A month input returns data in "YYYY-MM" format
    const monthVal = document.getElementById('filterRxMonth').value; 
    
    const filtered = cache.prescriptions.filter(rx => {
        if (!monthVal) return true; // If they clear the date, show all
        
        // Check if the prescription date (YYYY-MM-DD) starts with the chosen Year-Month (YYYY-MM)
        return rx.date && rx.date.startsWith(monthVal);
    });

    document.getElementById('myPrescriptionsContainer').innerHTML = prescriptionsListHTML(filtered);
}

// ============================================================
//   RECEPTIONIST — PATIENT TAB & FILTER LOGIC
// ============================================================
function togglePatientView(view) {
    const btnReg = document.getElementById('btnViewRegister');
    const btnList = document.getElementById('btnViewList');
    const secReg = document.getElementById('sectionRegisterPatientForm');
    const secList = document.getElementById('sectionRegisteredPatientsList');

    if (view === 'register') {
        secReg.style.display = 'block';
        secList.style.display = 'none';
        btnReg.className = 'btn btn-blue';
        btnList.className = 'btn';
        btnList.style.background = 'var(--bg)';
        btnList.style.color = 'var(--text)';
    } else {
        secReg.style.display = 'none';
        secList.style.display = 'block';
        btnList.className = 'btn btn-blue';
        btnReg.className = 'btn';
        btnReg.style.background = 'var(--bg)';
        btnReg.style.color = 'var(--text)';
    }
}

function filterPatients() {
    const gender = document.getElementById('filterPatientGender').value;
    const searchType = document.getElementById('filterPatientSearchType').value;
    const query = document.getElementById('filterPatientInput').value.toLowerCase();

    const filtered = cache.patients.filter(p => {
        const matchesGender = gender === 'All' || p.Gender === gender;
        let matchesSearch = true;
        
        if (query) {
            if (searchType === 'name') {
                matchesSearch = p.Name.toLowerCase().includes(query);
            } else if (searchType === 'id') {
                matchesSearch = String(p.PatientID).includes(query);
            }
        }
        
        return matchesGender && matchesSearch;
    });

    document.getElementById('patientsTableContainer').innerHTML = patientsTableHTML(filtered);
}

// ============================================================
//   RECEPTIONIST — APPOINTMENTS TAB LOGIC
// ============================================================
function toggleApptView(view) {
    const btnBook = document.getElementById('btnViewBookAppt');
    const btnList = document.getElementById('btnViewAllAppts');
    const secBook = document.getElementById('sectionBookAppt');
    const secList = document.getElementById('sectionAllAppts');

    if (!btnBook) return; // Failsafe for Admin view

    if (view === 'book') {
        secBook.style.display = 'block';
        secList.style.display = 'none';
        btnBook.className = 'btn btn-blue';
        btnList.className = 'btn';
        btnList.style.background = 'var(--bg)';
        btnList.style.color = 'var(--text)';
    } else {
        secBook.style.display = 'none';
        secList.style.display = 'block';
        btnList.className = 'btn btn-blue';
        btnBook.className = 'btn';
        btnBook.style.background = 'var(--bg)';
        btnBook.style.color = 'var(--text)';
    }
}

// ============================================================
//   RECEPTIONIST — ROOM ALLOCATE TAB LOGIC
// ============================================================
function toggleRoomAllocateView(view) {
    const btnAlloc = document.getElementById('btnViewAllocate');
    const btnList = document.getElementById('btnViewRoomStatus');
    const secAlloc = document.getElementById('sectionAllocateRoom');
    const secList = document.getElementById('sectionRoomStatusList');

    if (view === 'allocate') {
        secAlloc.style.display = 'block';
        secList.style.display = 'none';
        btnAlloc.className = 'btn btn-blue';
        btnList.className = 'btn';
        btnList.style.background = 'var(--bg)';
        btnList.style.color = 'var(--text)';
    } else {
        secAlloc.style.display = 'none';
        secList.style.display = 'block';
        btnList.className = 'btn btn-blue';
        btnAlloc.className = 'btn';
        btnAlloc.style.background = 'var(--bg)';
        btnAlloc.style.color = 'var(--text)';
    }
}

// ============================================================
//   RECEPTIONIST — BILLING TAB LOGIC
// ============================================================
function toggleBillingView(view) {
    const btnCreate = document.getElementById('btnViewCreateBill');
    const btnList = document.getElementById('btnViewAllBills');
    const secCreate = document.getElementById('sectionCreateBill');
    const secList = document.getElementById('sectionAllBills');

    if (!btnCreate) return; // Failsafe

    if (view === 'create') {
        secCreate.style.display = 'block';
        secList.style.display = 'none';
        btnCreate.className = 'btn btn-blue';
        btnList.className = 'btn';
        btnList.style.background = 'var(--bg)';
        btnList.style.color = 'var(--text)';
    } else {
        secCreate.style.display = 'none';
        secList.style.display = 'block';
        btnList.className = 'btn btn-blue';
        btnCreate.className = 'btn';
        btnCreate.style.background = 'var(--bg)';
        btnCreate.style.color = 'var(--text)';
    }
}
</script>

<!-- ======================== EDIT MODAL ======================== -->
<div id="editModal" class="modal-overlay">
    <div class="modal-box" style="max-width:500px">
        <h2 id="editModalTitle"><i class="fas fa-edit" style="color:var(--primary)"></i> Edit</h2>
        <div id="editModalBody"></div>
        <div style="display:flex;gap:10px;margin-top:16px">
            <button class="btn btn-blue" style="flex:1;justify-content:center" onclick="submitEdit()"><i class="fas fa-save"></i> Save</button>
            <button class="btn btn-red" style="flex:1;justify-content:center" onclick="closeEditModal()"><i class="fas fa-times"></i> Cancel</button>
        </div>
    </div>
</div>
</body>
</html>