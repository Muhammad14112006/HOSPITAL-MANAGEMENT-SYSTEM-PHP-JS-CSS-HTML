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

function hasPermission($permName) {
    // Admins get universal access by default
    if (role() === 'Admin') return true; 
    
    // Check if the specific permission exists in the user's session array
    $userPerms = $_SESSION['hms_user']['permissions'] ?? [];
    return in_array($permName, $userPerms);
}

function requirePermission($permName) {
    if (!hasPermission($permName)) {
        respond(['error' => "Access Denied: You are not authorized to carry out this function."]);
    }
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

        $stmt = $db->prepare("SELECT * FROM users WHERE name = ? AND role = ?");
        $stmt->execute([$name, $role]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($pass, $user['password'])) {
            // Fetch this user's specific granted permissions
            $permStmt = $db->prepare("
                SELECT p.name FROM user_permissions up 
                JOIN permissions p ON up.permission_id = p.id 
                WHERE up.user_id = ?
            ");
            $permStmt->execute([$user['id']]);
            $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

            $_SESSION['hms_user'] = [
                'id' => $user['id'], 
                'name' => $user['name'], 
                'role' => $user['role'], 
                'ref_id' => $user['ref_id'],
                'permissions' => $permissions // Save permissions to session
            ];
            respond(['success' => true, 'user' => $_SESSION['hms_user']]);
        }
        respond(['error' => "Invalid $role credentials"]);
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
        $dob    = $_POST['dob'] ?: null;
        $gender = $_POST['gender'] ?? null;
        $phone  = trim($_POST['phone'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $city   = trim($_POST['city'] ?? '');
        $zip    = trim($_POST['zipcode'] ?? '');
        $pass   = $_POST['password'] ?? '';
        
        if (!$name) respond(['error' => 'Patient name required']);
        if (strlen($pass) < 6 || strlen($pass) > 15) respond(['error' => 'Patient password must be between 6 and 15 characters!']);
        
        try {
            $db->beginTransaction();
            // Get the ID of the Receptionist currently logged in
            $recId = $_SESSION['hms_user']['ref_id'];
            
            $stmt = $db->prepare("INSERT INTO patients (Name, DateOfBirth, Gender, Phone, Street, City, ZipCode, registered_by) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $dob, $gender ?: null, $phone, $street, $city, $zip, $recId]);
            $pid = $db->lastInsertId();
            
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (name, password, role, ref_id) VALUES (?,?,'Patient',?)")
               ->execute([$name, $hash, $pid]);
               
            $db->commit();
            respond(['success' => true, 'id' => $pid]);
        } catch (Exception $e) {
            $db->rollBack();
            respond(['error' => 'Failed to register patient: ' . $e->getMessage()]);
        }
    }

    if ($action === 'delete_patient') {
        requireLogin();
        // NOW USING DYNAMIC RBAC!
        requirePermission('delete_patients'); 
        
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM users WHERE role='Patient' AND ref_id=?")->execute([$id]);
        $db->prepare("DELETE FROM patients WHERE PatientID=?")->execute([$id]);
        respond(['success' => true]);
    }
    if ($action === 'update_patient') {
        requireLogin();
        if (!in_array(role(), ['Admin','Receptionist'])) respond(['error' => 'Access denied']);
        
        $id      = (int)($_POST['id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $dob     = $_POST['dob'] ?: null; // Handle empty date as null
        $gender  = $_POST['gender'] ?? null;
        $phone   = trim($_POST['phone'] ?? '');
        $street  = trim($_POST['street'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $zip     = trim($_POST['zipcode'] ?? '');
        
        if (!$id) respond(['error' => 'Patient ID required']);
        if (!$name) respond(['error' => 'Name required']);
        
        try {
            $db->beginTransaction();
            $db->prepare("UPDATE patients SET Name=?, DateOfBirth=?, Gender=?, Phone=?, Street=?, City=?, ZipCode=? WHERE PatientID=?")
               ->execute([$name, $dob, $gender ?: null, $phone, $street, $city, $zip, $id]);
               
            $db->prepare("UPDATE users SET name=? WHERE role='Patient' AND ref_id=?")
               ->execute([$name, $id]);
               
            $db->commit();
            respond(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            respond(['error' => 'Failed to update patient: ' . $e->getMessage()]);
        }
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
        
        // If a Receptionist is booking this, log their ID. Otherwise, leave it NULL.
        $bookedBy = (role() === 'Receptionist') ? $_SESSION['hms_user']['ref_id'] : null;

        $stmt = $db->prepare("INSERT INTO appointments (Date, Diagnosis, Status, DoctorID, PatientID, booked_by) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$date, $diag, $status, $docId, $patId, $bookedBy]);
        $enrollId = $db->lastInsertId();
        // NOTE: Bill is now auto-generated by the DB trigger
        // trg_auto_bill_on_appointment (AFTER INSERT on appointments).
        // The trigger reads appointment_fee from doctors and creates a
        // Pending bill automatically — no PHP code needed here.
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
        $rows = $db->query("
            SELECT r.*, p.Name AS assignedName 
            FROM rooms r 
            LEFT JOIN patients p ON r.assignedTo = p.PatientID 
            ORDER BY r.RoomID
        ")->fetchAll();
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

        $room = $db->prepare("SELECT * FROM rooms WHERE RoomID=? AND RoomStatus='Available'");
        $room->execute([$roomId]);
        $room = $room->fetch();
        if (!$room) respond(['error' => 'Room not available']);

        $appt = $db->prepare("SELECT * FROM appointments WHERE EnrollmentID=?");
        $appt->execute([$enrollId]);
        $appt = $appt->fetch();
        if (!$appt) respond(['error' => 'Enrollment not found']);

        $amount = $room['price'] * $nights;
        $generatedBy = (role() === 'Receptionist') ? $_SESSION['hms_user']['ref_id'] : null;

        $db->prepare("UPDATE rooms SET RoomStatus='Occupied', assignedTo=?, enrollmentId=? WHERE RoomID=?")
            ->execute([$appt['PatientID'], $enrollId, $roomId]);

        $db->prepare("INSERT INTO bills (TotalAmount, PaymentStatus, BillDate, EnrollmentID, PatientID, RoomID, nights, generated_by) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$amount, 'Pending', today(), $enrollId, $appt['PatientID'], $roomId, $nights, $generatedBy]);

        respond(['success' => true, 'amount' => $amount, 'bill_id' => $db->lastInsertId()]);
    }
    if ($action === 'discharge_room') {
        requireLogin();
        requirePermission('override_rooms'); // Locks down discharging
        
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE rooms SET RoomStatus='Available', assignedTo=NULL, enrollmentId=NULL WHERE RoomID=?")
            ->execute([$id]);
        respond(['success' => true]);
    }
    
    if ($action === 'mark_available') {
        requireLogin();
        requirePermission('override_rooms'); // Locks down maintenance overrides
        
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
        
        if (!$patId || !$enrollId || $amount <= 0) respond(['error' => 'Fill all fields correctly']);
        
        $generatedBy = (role() === 'Receptionist') ? $_SESSION['hms_user']['ref_id'] : null;
        
        $db->prepare("INSERT INTO bills (TotalAmount, PaymentStatus, BillDate, EnrollmentID, PatientID, RoomID, generated_by) VALUES (?,?,?,?,?,?,?)")
           ->execute([$amount, $status, $date, $enrollId, $patId, $roomId ?: null, $generatedBy]);
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
        // NOW USING DYNAMIC RBAC!
        requirePermission('delete_bills'); 
        
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
            $stmt = $db->prepare("INSERT INTO prescriptions (EnrollmentID, PatientID, DoctorID, instructions, date) VALUES (?,?,?,?,?)");
            $stmt->execute([$enrollId, $patId, $docId, $inst, today()]);
            $rxId = $db->lastInsertId();

            $medStmt = $db->prepare("INSERT INTO prescription_items (rx_id, med_id, dosage) VALUES (?,?,?)");
            foreach ($meds as $med) {
                $medStmt->execute([$rxId, $med['med_id'], $med['dosage']]);
            }
            
            // NOTE: appointment Status is set to 'Treated' automatically
            // by the DB trigger trg_auto_treated_on_prescription
            // (AFTER INSERT on prescriptions) — no PHP code needed here.
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
        
        $stmt = $db->prepare("
            SELECT p.rx_id, p.date, p.instructions, d.name AS doctor_name, a.Diagnosis
            FROM prescriptions p
            JOIN doctors d ON p.DoctorID = d.id
            JOIN appointments a ON p.EnrollmentID = a.EnrollmentID
            WHERE p.PatientID = ? ORDER BY p.rx_id DESC
        ");
        $stmt->execute([$patId]);
        $prescriptions = $stmt->fetchAll();

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
        
        $stmt = $db->query("SELECT password FROM users WHERE role='Doctor'");
        while ($row = $stmt->fetch()) {
            if (password_verify($pass, $row['Passwords'])) {
                respond(['error' => 'This password is already taken by another doctor. Try a different password.']);
            }
        }
        
        try {
            $db->beginTransaction();
            $db->prepare("INSERT INTO doctors (name, specialization, phone, salary, dept_id, appointment_fee) VALUES (?,?,?,?,?,?)")
               ->execute([$name, $spec, $phone, $salary, $dept, $fee]);
            $docId = $db->lastInsertId();
            
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (name, password, role, ref_id) VALUES (?,?,'Doctor',?)")
               ->execute([$name, $hash, $docId]);
               
            $db->commit();
            respond(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            respond(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    if ($action === 'save_receptionist') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Access denied']);
        
        $name = trim($_POST['name'] ?? '');
        $pass = $_POST['password'] ?? '';
        $salary = (float)($_POST['salary'] ?? 0);
        
        if (strlen($pass) < 6 || strlen($pass) > 15) respond(['error' => 'Receptionist password must be between 6 and 15 characters!']);
        
        $stmt = $db->query("SELECT password FROM users WHERE role='Receptionist'");
        while ($row = $stmt->fetch()) {
            if (password_verify($pass, $row['password'])) {
                respond(['error' => 'This password is already taken by another receptionist. Try a different password.']);
            }
        }
        
       try {
            $db->beginTransaction();
            $db->prepare("INSERT INTO receptionists (name, salary) VALUES (?,?)")->execute([$name, $salary]);
            $recId = $db->lastInsertId();
            
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (name, password, role, ref_id) VALUES (?,?,'Receptionist',?)")
               ->execute([$name, $hash, $recId]);
               
            $db->commit();
            respond(['success' => true]);
        } catch(Exception $e) {
            $db->rollBack();
            respond(['error' => 'Error: ' . $e->getMessage()]);
        }
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
        
        if (!$targetRole || !$targetName || !$newPass) respond(['error' => 'All fields required.']);
        if (strlen($newPass) < 6 || strlen($newPass) > 15) respond(['error' => 'New password must be between 6 and 15 characters.']);
        
        $stmt = $db->prepare("SELECT id FROM users WHERE name = ? AND role = ?");
        $stmt->execute([$targetName, $targetRole]);
        if (!$stmt->fetch()) respond(['error' => "User '{$targetName}' not found in the system."]);
        
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = ? WHERE name = ? AND role = ?")
           ->execute([$hash, $targetName, $targetRole]);
           
        respond(['success' => true]);
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

    // ---- ACCESS CONTROL (GRANT / REVOKE) ----
    if ($action === 'get_access_control') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Admin only']);
        
        // ONLY fetch Receptionists, and EXCLUDE the manage_departments permission
        $users = $db->query("SELECT id, name, role FROM users WHERE role = 'Receptionist' ORDER BY name")->fetchAll();
        $perms = $db->query("SELECT * FROM permissions WHERE name != 'manage_departments'")->fetchAll();
        $userPerms = $db->query("SELECT user_id, permission_id FROM user_permissions")->fetchAll();
        
        respond(['users' => $users, 'permissions' => $perms, 'active_grants' => $userPerms]);
    }

    if ($action === 'grant_permission') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Admin only']);
        $userId = (int)($_POST['user_id'] ?? 0);
        $permId = (int)($_POST['permission_id'] ?? 0);
        
        // Use IGNORE so it doesn't crash if they already have it
        $db->prepare("INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (?, ?)")->execute([$userId, $permId]);
        respond(['success' => true]);
    }

    if ($action === 'revoke_permission') {
        requireLogin();
        if (role() !== 'Admin') respond(['error' => 'Admin only']);
        $userId = (int)($_POST['user_id'] ?? 0);
        $permId = (int)($_POST['permission_id'] ?? 0);
        
        $db->prepare("DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?")->execute([$userId, $permId]);
        respond(['success' => true]);
    }

    respond(['error' => 'Unknown API action']);
    exit;
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare HMS — Compassionate Healthcare Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Space+Grotesk:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* =====================================================================
   MEDICARE HMS — HEART-TOUCHING MEDICAL DESIGN SYSTEM v2
   "Where Technology Meets Compassion"
   ===================================================================== */

/* ---- RESET & BASE ---- */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
    font-family:'Plus Jakarta Sans',sans-serif;
    background:#060d1a;
    color:#dde8f5;
    line-height:1.6;
    overflow-x:hidden;
}
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:#060d1a}
::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#06b6d4,#3b82f6);border-radius:3px}

/* ---- CSS VARIABLES ---- */
:root{
    /* Core brand */
    --navy:    #060d1a;
    --navy2:   #0a1628;
    --navy3:   #0f2040;
    --teal:    #06b6d4;
    --teal-d:  #0891b2;
    --teal-lt: rgba(6,182,212,.12);
    --blue:    #3b82f6;
    --green:   #10b981;
    --red:     #ef4444;
    --gold:    #f59e0b;
    --purple:  #8b5cf6;

    /* Text */
    --t1:#f0f8ff;
    --t2:#94b4d0;
    --t3:#3d6585;

    /* Glass surfaces */
    --glass:   rgba(10,22,50,.75);
    --glass-b: rgba(6,182,212,.12);
    --glass-bd:1px solid rgba(6,182,212,.18);

    /* Glows */
    --glow-teal: 0 0 28px rgba(6,182,212,.35), 0 0 60px rgba(6,182,212,.12);
    --glow-blue: 0 0 28px rgba(59,130,246,.35);

    /* Legacy aliases — keep for JS-generated inline styles */
    --primary:    #1d5eb8;
    --primary-lt: #2563eb;
    --accent:     #f59e0b;
    --success:    #10b981;
    --danger:     #ef4444;
    --warn:       #f59e0b;
    --dark:       #060d1a;
    --text:       #dde8f5;
    --muted:      #6a8fa8;
    --border:     rgba(6,182,212,.18);
    --bg:         #0a1628;
    --white:      rgba(255,255,255,.05);
    --shadow:     0 8px 32px rgba(0,0,0,.55);
    --radius:     14px;
}

/* ---- PAGE VISIBILITY ---- */
.page{display:none}.active-page{display:block}

/* =====================================================================
   BUTTONS
   ===================================================================== */
.btn{
    display:inline-flex;align-items:center;gap:8px;
    padding:12px 26px;border-radius:12px;border:none;
    font-family:'Plus Jakarta Sans',sans-serif;font-size:.875rem;font-weight:700;
    cursor:pointer;transition:all .3s cubic-bezier(.4,0,.2,1);
    position:relative;overflow:hidden;white-space:nowrap;letter-spacing:.01em;
    text-decoration:none;
}
.btn::after{
    content:'';position:absolute;inset:0;
    background:linear-gradient(135deg,rgba(255,255,255,.18) 0%,transparent 55%);
    opacity:0;transition:.3s;
}
.btn:hover::after{opacity:1}
.btn:active{transform:scale(.97)}

.btn-blue{
    background:linear-gradient(135deg,#1d5eb8,#2563eb);
    color:#fff;
    box-shadow:0 4px 20px rgba(37,99,235,.4),inset 0 1px 0 rgba(255,255,255,.12);
}
.btn-blue:hover{box-shadow:0 6px 30px rgba(37,99,235,.6),inset 0 1px 0 rgba(255,255,255,.18);transform:translateY(-2px)}

.btn-teal{
    background:linear-gradient(135deg,#0891b2,#06b6d4);
    color:#fff;
    box-shadow:var(--glow-teal),inset 0 1px 0 rgba(255,255,255,.12);
}
.btn-teal:hover{box-shadow:0 0 40px rgba(6,182,212,.6),0 6px 30px rgba(6,182,212,.3);transform:translateY(-2px)}

.btn-red{
    background:linear-gradient(135deg,#b91c1c,#ef4444);
    color:#fff;
    box-shadow:0 4px 20px rgba(239,68,68,.35);
}
.btn-red:hover{box-shadow:0 6px 28px rgba(239,68,68,.55);transform:translateY(-2px)}

.btn-green{
    background:linear-gradient(135deg,#059669,#10b981);
    color:#fff;
    box-shadow:0 4px 20px rgba(16,185,129,.35);
}
.btn-green:hover{box-shadow:0 6px 28px rgba(16,185,129,.55);transform:translateY(-2px)}

.btn-orange{
    background:linear-gradient(135deg,#d97706,#f59e0b);
    color:#fff;
    box-shadow:0 4px 20px rgba(245,158,11,.35);
}
.btn-orange:hover{box-shadow:0 6px 28px rgba(245,158,11,.55);transform:translateY(-2px)}

.btn-ghost{
    background:rgba(255,255,255,.06);
    color:var(--t1);
    border:1.5px solid rgba(255,255,255,.18);
    backdrop-filter:blur(8px);
}
.btn-ghost:hover{background:rgba(6,182,212,.1);border-color:rgba(6,182,212,.4);transform:translateY(-2px);box-shadow:0 0 24px rgba(6,182,212,.15)}

.btn-sm{padding:7px 16px;font-size:.78rem;border-radius:8px}
.btn-lg{padding:16px 42px;font-size:1.05rem;border-radius:14px}

/* =====================================================================
   FORMS
   ===================================================================== */
.form-group{margin-bottom:18px}
.form-group label{
    display:block;font-size:.73rem;font-weight:700;
    letter-spacing:.7px;text-transform:uppercase;
    color:var(--t2);margin-bottom:7px;
}
input,select,textarea{
    width:100%;padding:13px 17px;
    background:rgba(255,255,255,.05);
    border:1.5px solid rgba(6,182,212,.15);
    border-radius:11px;
    font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;color:var(--t1);
    outline:none;transition:.25s;backdrop-filter:blur(8px);
}
input::placeholder,textarea::placeholder{color:var(--t3)}
input:focus,select:focus,textarea:focus{
    border-color:var(--teal);
    background:rgba(6,182,212,.06);
    box-shadow:0 0 0 3px rgba(6,182,212,.12),var(--glow-teal);
}
input:disabled{
    background:rgba(255,255,255,.02);color:var(--t3);
    border-color:rgba(255,255,255,.05);cursor:not-allowed;
}
select option{background:#0a1628;color:#f0f8ff}
textarea{resize:vertical;min-height:90px}
.form-row{display:grid;gap:18px}
.form-row.col2{grid-template-columns:1fr 1fr}
.form-row.col3{grid-template-columns:1fr 1fr 1fr}

/* =====================================================================
   CARDS
   ===================================================================== */
.card{
    background:rgba(10,22,50,.75);
    border:1px solid rgba(6,182,212,.1);
    border-radius:18px;padding:30px;margin-bottom:24px;
    backdrop-filter:blur(24px);
    box-shadow:0 8px 32px rgba(0,0,0,.45),
               inset 0 1px 0 rgba(255,255,255,.05);
    position:relative;overflow:hidden;
}
.card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:1px;
    background:linear-gradient(90deg,transparent,rgba(6,182,212,.5),transparent);
}
.card h3{
    font-family:'Space Grotesk',sans-serif;
    font-size:1.02rem;font-weight:700;
    color:var(--teal);margin-bottom:20px;
    display:flex;align-items:center;gap:9px;
}

/* =====================================================================
   PILLS / STATUS BADGES
   ===================================================================== */
.pill{
    padding:4px 13px;border-radius:20px;
    font-size:.7rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.7px;
    display:inline-block;border:1px solid transparent;
}
.pill-green {background:rgba(16,185,129,.15);color:#34d399;border-color:rgba(16,185,129,.3)}
.pill-yellow{background:rgba(245,158,11,.15);color:#fbbf24;border-color:rgba(245,158,11,.3)}
.pill-red   {background:rgba(239,68,68,.15);color:#f87171;border-color:rgba(239,68,68,.3)}
.pill-blue  {background:rgba(99,102,241,.15);color:#818cf8;border-color:rgba(99,102,241,.3)}
.pill-gray  {background:rgba(100,116,139,.15);color:#94a3b8;border-color:rgba(100,116,139,.25)}

/* =====================================================================
   TABLES
   ===================================================================== */
.tbl-wrap{overflow-x:auto;border-radius:14px}
table{width:100%;border-collapse:collapse;font-size:.84rem}
th{
    background:rgba(6,182,212,.08);color:var(--teal);
    font-weight:800;font-size:.7rem;
    text-transform:uppercase;letter-spacing:1px;
    padding:15px 20px;text-align:left;
    border-bottom:1px solid rgba(6,182,212,.12);
}
td{
    padding:14px 20px;
    border-bottom:1px solid rgba(255,255,255,.04);
    color:var(--t1);vertical-align:middle;transition:.2s;
}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(6,182,212,.04)}

/* =====================================================================
   ALERTS
   ===================================================================== */
.alert{
    padding:13px 18px;border-radius:11px;
    margin-bottom:18px;font-size:.87rem;font-weight:600;
    border:1px solid transparent;
}
.alert-error{background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.25)}
.alert-success{background:rgba(16,185,129,.12);color:#34d399;border-color:rgba(16,185,129,.25)}

/* =====================================================================
   LOADING
   ===================================================================== */
.loading{text-align:center;padding:70px}
.loading i{
    font-size:2.8rem;color:var(--teal);
    animation:spin 1s linear infinite;
    filter:drop-shadow(0 0 14px var(--teal));
}
@keyframes spin{to{transform:rotate(360deg)}}

/* =====================================================================
   MODAL — GLASS PREMIUM
   ===================================================================== */
.modal-overlay{
    display:none;position:fixed;inset:0;
    background:rgba(2,8,20,.82);z-index:2000;
    justify-content:center;align-items:center;
    backdrop-filter:blur(16px);
}
.modal-overlay.open{display:flex;animation:modal-in .35s cubic-bezier(.34,1.56,.64,1) both}
@keyframes modal-in{from{opacity:0;transform:scale(.88)}to{opacity:1;transform:scale(1)}}

.modal-box{
    background:rgba(6,18,48,.94);
    border:1px solid rgba(6,182,212,.22);
    border-radius:24px;width:100%;max-width:450px;
    padding:44px;position:relative;overflow:hidden;
    box-shadow:0 30px 90px rgba(0,0,0,.75),var(--glow-teal),
               inset 0 1px 0 rgba(255,255,255,.05);
    backdrop-filter:blur(40px);
}
.modal-box::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,var(--teal),var(--blue),var(--green));
}
.modal-box h2{
    font-family:'Space Grotesk',sans-serif;font-size:1.45rem;font-weight:800;
    color:var(--t1);margin-bottom:30px;
    display:flex;align-items:center;gap:11px;
}

/* =====================================================================
   ██  LANDING PAGE  ██
   ===================================================================== */

/* ---- NAV ---- */
.main-nav{
    display:flex;justify-content:space-between;align-items:center;
    padding:0 6%;height:78px;
    background:rgba(6,13,26,.9);
    backdrop-filter:blur(28px) saturate(180%);
    border-bottom:1px solid rgba(6,182,212,.1);
    position:sticky;top:0;z-index:1000;
    transition:.3s;
}
.main-nav::after{
    content:'';position:absolute;bottom:0;left:0;right:0;height:1px;
    background:linear-gradient(90deg,transparent,rgba(6,182,212,.55),transparent);
}

/* Logo with beating heart cross */
.logo{
    display:flex;align-items:center;gap:14px;
    font-family:'Space Grotesk',sans-serif;
    font-size:1.5rem;font-weight:800;color:var(--t1);
    letter-spacing:-.5px;text-decoration:none;
}
.logo-icon{
    width:44px;height:44px;border-radius:12px;
    background:linear-gradient(135deg,#dc2626,#ef4444);
    display:flex;align-items:center;justify-content:center;
    font-size:1.35rem;color:#fff;
    box-shadow:0 0 22px rgba(239,68,68,.5),0 0 44px rgba(239,68,68,.2);
    animation:heart-glow 1.4s ease-in-out infinite alternate;
}
@keyframes heart-glow{
    from{box-shadow:0 0 18px rgba(239,68,68,.4),0 0 36px rgba(239,68,68,.15)}
    to{box-shadow:0 0 32px rgba(239,68,68,.7),0 0 60px rgba(239,68,68,.3)}
}
.logo i{animation:heartbeat 1.4s ease-in-out infinite}
@keyframes heartbeat{0%,100%{transform:scale(1)}14%{transform:scale(1.28)}28%{transform:scale(1.05)}42%{transform:scale(1.22)}70%{transform:scale(1)}}

nav.main-links a{
    margin:0 14px;text-decoration:none;
    color:var(--t2);font-weight:600;font-size:.9rem;
    transition:.25s;position:relative;
}
nav.main-links a::after{
    content:'';position:absolute;bottom:-5px;left:0;right:0;
    height:2px;background:var(--teal);border-radius:2px;
    transform:scaleX(0);transition:.25s;
}
nav.main-links a:hover{color:var(--teal)}
nav.main-links a:hover::after{transform:scaleX(1)}

/* ---- ECG STRIP (runs across full page in hero) ---- */
.ecg-strip{
    position:absolute;bottom:60px;left:0;width:100%;height:70px;
    z-index:3;pointer-events:none;overflow:hidden;opacity:.55;
}
.ecg-strip svg{width:200%;height:100%;animation:ecg-scroll 5s linear infinite}
@keyframes ecg-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ---- HERO ---- */
.hero{
    position:relative;min-height:100vh;overflow:hidden;
    display:flex;align-items:center;
    background:radial-gradient(ellipse 80% 70% at 60% 30%,rgba(6,182,212,.09) 0%,transparent 60%),
               radial-gradient(ellipse 60% 50% at 20% 80%,rgba(139,92,246,.08) 0%,transparent 55%),
               linear-gradient(170deg,#060d1a 0%,#080f1f 40%,#06101c 100%);
}

/* Animated dot grid */
.hero::before{
    content:'';position:absolute;inset:0;z-index:0;
    background-image:radial-gradient(rgba(6,182,212,.2) 1px,transparent 1px);
    background-size:44px 44px;
    animation:grid-breathe 8s ease-in-out infinite alternate;
    mask-image:radial-gradient(ellipse 85% 80% at 55% 45%,black 30%,transparent 100%);
}
@keyframes grid-breathe{from{opacity:.35}to{opacity:.6}}

/* Orbital rings around hero visual */
.orbital-ring{
    position:absolute;border-radius:50%;border:1px solid rgba(6,182,212,.18);
    animation:orbit-spin linear infinite;pointer-events:none;
}

/* Floating blobs */
.blob{position:absolute;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0}
.blob-1{width:680px;height:680px;top:-150px;right:-100px;
    background:radial-gradient(circle,rgba(6,182,212,.18) 0%,transparent 65%);
    animation:blob-drift 12s ease-in-out infinite alternate}
.blob-2{width:500px;height:500px;bottom:-100px;left:-80px;
    background:radial-gradient(circle,rgba(139,92,246,.2) 0%,transparent 65%);
    animation:blob-drift 15s ease-in-out infinite alternate-reverse}
.blob-3{width:380px;height:380px;top:40%;left:38%;
    background:radial-gradient(circle,rgba(16,185,129,.14) 0%,transparent 65%);
    animation:blob-drift 18s ease-in-out infinite alternate}
.blob-4{width:260px;height:260px;top:8%;left:12%;
    background:radial-gradient(circle,rgba(239,68,68,.1) 0%,transparent 65%);
    animation:blob-drift 10s ease-in-out infinite alternate-reverse}
@keyframes blob-drift{0%{transform:translate(0,0) scale(1)}100%{transform:translate(35px,22px) scale(1.1)}}

/* Medical cross floating symbol */
.medical-cross{
    position:absolute;top:12%;right:8%;width:80px;height:80px;
    z-index:2;opacity:.18;
    animation:cross-float 6s ease-in-out infinite alternate;
}
.medical-cross::before,.medical-cross::after{
    content:'';position:absolute;background:var(--teal);border-radius:4px;
}
.medical-cross::before{width:30%;height:100%;left:35%}
.medical-cross::after{width:100%;height:30%;top:35%}
@keyframes cross-float{from{transform:translateY(0) rotate(0deg)}to{transform:translateY(-22px) rotate(8deg)}}

/* Particle icons */
.hero-particles{position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden}
.mp{
    position:absolute;opacity:0;
    animation:mp-float linear infinite;
    color:rgba(6,182,212,.3);
}
@keyframes mp-float{
    0%{opacity:0;transform:translateY(30px) scale(.7)}
    15%{opacity:1}
    85%{opacity:.4}
    100%{opacity:0;transform:translateY(-160px) scale(1.15)}
}

.hero-inner{
    position:relative;z-index:4;
    display:grid;grid-template-columns:1fr 1fr;
    gap:60px;align-items:center;
    padding:120px 7% 100px;
    width:100%;max-width:1440px;margin:0 auto;
}

/* ---- HERO CONTENT ---- */
.hero-content{}
.hero-badge{
    display:inline-flex;align-items:center;gap:10px;
    background:rgba(6,182,212,.08);
    border:1px solid rgba(6,182,212,.28);
    border-radius:32px;padding:9px 22px;
    font-size:.73rem;font-weight:800;
    color:var(--teal);letter-spacing:1.2px;text-transform:uppercase;
    margin-bottom:30px;
    animation:fade-up .7s ease both;
}
.live-dot{
    width:9px;height:9px;border-radius:50%;
    background:var(--teal);
    box-shadow:0 0 10px var(--teal),0 0 20px rgba(6,182,212,.5);
    animation:live-pulse 1.3s ease-in-out infinite;
}
@keyframes live-pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.5);opacity:.5}}

.hero-content h1{
    font-family:'Space Grotesk',sans-serif;
    font-size:clamp(2.6rem,5.2vw,4.4rem);
    font-weight:800;line-height:1.07;letter-spacing:-2.5px;
    margin-bottom:6px;
    animation:fade-up .7s .08s ease both;
}
.h1-line1{color:#f0f8ff;display:block}
.h1-line2{
    background:linear-gradient(90deg,#06b6d4 0%,#3b82f6 40%,#8b5cf6 75%,#10b981 100%);
    background-size:200% auto;
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    background-clip:text;display:block;
    animation:fade-up .7s .16s ease both, grad-move 5s linear infinite;
}
@keyframes grad-move{to{background-position:200% center}}
@keyframes fade-up{from{opacity:0;transform:translateY(26px)}to{opacity:1;transform:translateY(0)}}

.hero-sub{
    color:#7aa8c2;font-size:1.08rem;line-height:1.85;
    margin:22px 0 40px;max-width:490px;
    animation:fade-up .7s .24s ease both;
}
.hero-sub strong{color:#06b6d4;font-weight:700}

.hero-btns{
    display:flex;gap:16px;flex-wrap:wrap;
    animation:fade-up .7s .32s ease both;
}

/* Trust line below buttons */
.hero-trust{
    display:flex;align-items:center;gap:24px;
    margin-top:28px;
    animation:fade-up .7s .4s ease both;
}
.trust-item{display:flex;align-items:center;gap:7px;color:var(--t2);font-size:.82rem;font-weight:600}
.trust-item i{color:var(--green);font-size:.9rem}

/* ---- HERO VISUAL — Medical Dashboard Card ---- */
.hero-visual{
    display:flex;justify-content:center;align-items:center;
    perspective:1200px;position:relative;
}
/* Orbital rings around dashboard card */
.orb-ring{
    position:absolute;border-radius:50%;
    border:1px solid rgba(6,182,212,.2);
    pointer-events:none;
}
.orb-ring-1{width:460px;height:460px;animation:orbit-spin 25s linear infinite}
.orb-ring-2{width:560px;height:560px;border-color:rgba(139,92,246,.1);animation:orbit-spin 35s linear infinite reverse}
.orb-ring-3{width:660px;height:660px;border-color:rgba(16,185,129,.08);border-style:dashed;animation:orbit-spin 50s linear infinite}
@keyframes orbit-spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

/* Floating mini-chips around main card */
.float-chip{
    position:absolute;z-index:6;
    background:rgba(10,22,50,.9);
    border:1px solid rgba(6,182,212,.2);
    border-radius:12px;padding:10px 14px;
    backdrop-filter:blur(20px);
    display:flex;align-items:center;gap:9px;
    font-size:.75rem;font-weight:700;
    box-shadow:0 8px 24px rgba(0,0,0,.4);
    white-space:nowrap;
    animation:chip-float ease-in-out infinite alternate;
}
.chip-top-right{top:-18px;right:-50px;animation-duration:4s;animation-delay:.3s;color:#34d399}
.chip-bottom-left{bottom:40px;left:-60px;animation-duration:5s;animation-delay:1.2s;color:#fbbf24}
.chip-top-left{top:80px;left:-55px;animation-duration:4.5s;animation-delay:.7s;color:#818cf8}
@keyframes chip-float{from{transform:translateY(0)}to{transform:translateY(-14px)}}

.hero-dashboard{
    width:370px;position:relative;z-index:5;
    transform-style:preserve-3d;
    animation:dash-float 8s ease-in-out infinite;
}
@keyframes dash-float{
    0%,100%{transform:rotateY(-16deg) rotateX(7deg) translateY(0)}
    50%{transform:rotateY(-11deg) rotateX(10deg) translateY(-18px)}
}
.hero-dashboard:hover{animation:none;transform:rotateY(0) rotateX(0) scale(1.05)}

.dash-card{
    background:rgba(6,16,42,.8);
    border:1px solid rgba(6,182,212,.22);
    border-radius:26px;padding:32px;
    backdrop-filter:blur(40px);
    box-shadow:0 30px 80px rgba(0,0,0,.7),var(--glow-teal),
               inset 0 1px 0 rgba(255,255,255,.06);
    overflow:hidden;position:relative;
}
.dash-card::before{
    content:'';position:absolute;top:-90px;right:-90px;
    width:280px;height:280px;border-radius:50%;
    background:radial-gradient(circle,rgba(6,182,212,.18) 0%,transparent 65%);
    pointer-events:none;
}
/* Animated scanning line inside card */
.dash-scan{
    position:absolute;left:0;right:0;height:2px;
    background:linear-gradient(90deg,transparent,rgba(6,182,212,.8),transparent);
    animation:dash-scan-move 3s linear infinite;top:0;
}
@keyframes dash-scan-move{from{top:0;opacity:1}to{top:100%;opacity:0}}

.dc-header{
    display:flex;align-items:center;gap:12px;margin-bottom:24px;
}
.dc-logo-sm{
    width:42px;height:42px;border-radius:11px;
    background:linear-gradient(135deg,#dc2626,#ef4444);
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;color:#fff;
    box-shadow:0 0 18px rgba(239,68,68,.5);
}
.dc-title{color:#f0f8ff;font-family:'Space Grotesk',sans-serif;font-weight:800;font-size:.95rem}
.dc-sub{color:#6a8fa8;font-size:.72rem;margin-top:2px}

.dc-big-metric{
    background:rgba(255,255,255,.04);
    border-radius:16px;padding:18px 20px;
    border:1px solid rgba(6,182,212,.1);
    margin-bottom:16px;position:relative;overflow:hidden;
}
.dc-big-metric::before{
    content:'';position:absolute;top:0;left:0;right:0;height:1px;
    background:linear-gradient(90deg,transparent,rgba(6,182,212,.45),transparent);
}
.dc-ml{color:#6ab5d8;font-size:.68rem;text-transform:uppercase;letter-spacing:.9px;font-weight:800;margin-bottom:8px}
.dc-mv{color:#f0f8ff;font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:800;letter-spacing:-1px}
.dc-mt{color:#34d399;font-size:.73rem;font-weight:700;margin-left:6px}

.dc-mini-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px}
.dc-mini{
    background:rgba(255,255,255,.04);
    border-radius:13px;padding:13px 15px;
    border:1px solid rgba(255,255,255,.06);
}
.dc-mini .v{color:var(--teal);font-family:'Space Grotesk',sans-serif;font-size:1.35rem;font-weight:800;text-shadow:0 0 14px rgba(6,182,212,.6)}
.dc-mini .l{color:#6a8fa8;font-size:.67rem;margin-top:3px}

/* Vital signs bar */
.dc-vitals{display:flex;align-items:center;gap:10px}
.vital-dot{width:10px;height:10px;border-radius:50%;background:#34d399;box-shadow:0 0 10px #34d399,0 0 22px rgba(52,211,153,.5);animation:live-pulse 1.5s infinite}
.vital-label{color:#34d399;font-size:.72rem;font-weight:800}
.vital-ecg{flex:1;height:34px;overflow:hidden}
.vital-ecg svg{width:100%;height:100%}

/* ---- TICKER ---- */
.ticker-wrap{
    background:#050c18;
    border-top:1px solid rgba(6,182,212,.08);
    border-bottom:1px solid rgba(6,182,212,.08);
    padding:14px 0;overflow:hidden;position:relative;
}
.ticker-wrap::before,.ticker-wrap::after{
    content:'';position:absolute;top:0;bottom:0;width:100px;z-index:2;
}
.ticker-wrap::before{left:0;background:linear-gradient(90deg,#050c18,transparent)}
.ticker-wrap::after{right:0;background:linear-gradient(-90deg,#050c18,transparent)}
.ticker-track{
    display:flex;gap:0;
    animation:ticker-move 28s linear infinite;
    width:max-content;
}
.ticker-item{
    display:flex;align-items:center;gap:10px;
    padding:0 40px;font-size:.8rem;font-weight:700;
    color:var(--t2);white-space:nowrap;
}
.ticker-item i{color:var(--teal);font-size:.85rem}
.ticker-sep{color:rgba(6,182,212,.3);font-size:1.2rem;margin:0 -10px}
@keyframes ticker-move{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ---- STATS SECTION (animated counters) ---- */
.stats-section{
    padding:90px 7%;
    background:linear-gradient(135deg,#050d1a 0%,#0a1826 50%,#050d1a 100%);
    position:relative;overflow:hidden;
}
.stats-section::before{
    content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse 70% 100% at 50% 50%,rgba(6,182,212,.05) 0%,transparent 70%);
}
.stats-inner{
    display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:30px;max-width:1200px;margin:0 auto;
    position:relative;z-index:1;
}
.stat-counter-card{
    text-align:center;padding:40px 24px;
    background:rgba(6,18,48,.7);
    border:1px solid rgba(6,182,212,.1);
    border-radius:22px;backdrop-filter:blur(24px);
    transition:.35s;position:relative;overflow:hidden;
}
.stat-counter-card::before{
    content:'';position:absolute;bottom:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,transparent,var(--teal),transparent);
    opacity:0;transition:.35s;
}
.stat-counter-card:hover{transform:translateY(-8px);border-color:rgba(6,182,212,.3);box-shadow:var(--glow-teal)}
.stat-counter-card:hover::before{opacity:1}
.stat-counter-icon{
    width:60px;height:60px;border-radius:16px;margin:0 auto 18px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.6rem;
}
.stat-counter-card:nth-child(1) .stat-counter-icon{background:rgba(6,182,212,.12);color:var(--teal)}
.stat-counter-card:nth-child(2) .stat-counter-icon{background:rgba(16,185,129,.12);color:var(--green)}
.stat-counter-card:nth-child(3) .stat-counter-icon{background:rgba(239,68,68,.12);color:var(--red)}
.stat-counter-card:nth-child(4) .stat-counter-icon{background:rgba(245,158,11,.12);color:var(--gold)}
.stat-counter-card:nth-child(5) .stat-counter-icon{background:rgba(139,92,246,.12);color:var(--purple)}
.stat-counter-card:nth-child(6) .stat-counter-icon{background:rgba(59,130,246,.12);color:var(--blue)}
.counter-number{
    font-family:'Space Grotesk',sans-serif;
    font-size:2.8rem;font-weight:800;letter-spacing:-2px;
    color:#f0f8ff;margin-bottom:6px;
    text-shadow:0 0 30px rgba(6,182,212,.2);
}
.counter-label{color:var(--t2);font-size:.88rem;font-weight:600}
.counter-sub{color:var(--t3);font-size:.75rem;margin-top:4px}

/* ---- SERVICES ---- */
.services{
    padding:120px 7%;
    background:var(--navy);position:relative;overflow:hidden;
}
.services::after{
    content:'';position:absolute;top:-200px;right:-150px;
    width:600px;height:600px;border-radius:50%;
    background:radial-gradient(circle,rgba(6,182,212,.06) 0%,transparent 70%);
    pointer-events:none;
}
.section-header{margin-bottom:64px}
.eyebrow{
    display:inline-block;
    background:rgba(6,182,212,.1);
    border:1px solid rgba(6,182,212,.22);
    border-radius:24px;padding:7px 20px;
    font-size:.71rem;font-weight:800;
    letter-spacing:1.4px;text-transform:uppercase;
    color:var(--teal);margin-bottom:18px;
}
.section-header h2{
    font-family:'Space Grotesk',sans-serif;
    font-size:clamp(2rem,4vw,3rem);font-weight:800;
    color:var(--t1);letter-spacing:-1.5px;margin-bottom:14px;
}
.section-header p{color:var(--t2);font-size:1.05rem;max-width:520px;line-height:1.75}

.services-grid{
    display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:26px;position:relative;z-index:1;
}
.svc-card{
    padding:42px 32px;border-radius:22px;
    background:rgba(6,16,42,.75);
    border:1px solid rgba(255,255,255,.07);
    backdrop-filter:blur(20px);
    transition:all .4s cubic-bezier(.175,.885,.32,1.275);
    position:relative;overflow:hidden;
}
.svc-card::before{
    content:'';position:absolute;inset:0;opacity:0;
    background:linear-gradient(135deg,rgba(6,182,212,.07) 0%,rgba(59,130,246,.04) 100%);
    transition:.4s;
}
.svc-card::after{
    content:'';position:absolute;top:0;left:-100%;right:100%;height:2px;
    background:var(--grad-teal,linear-gradient(90deg,var(--teal),var(--blue)));
    transition:.4s;
}
.svc-card:hover{transform:translateY(-12px);border-color:rgba(6,182,212,.28);box-shadow:0 24px 60px rgba(0,0,0,.5),0 0 40px rgba(6,182,212,.1)}
.svc-card:hover::before{opacity:1}
.svc-card:hover::after{left:0;right:0}
.svc-icon{
    width:66px;height:66px;border-radius:18px;margin-bottom:22px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.65rem;transition:.35s;
}
.svc-card:hover .svc-icon{transform:scale(1.12) rotate(-6deg)}
.svc-card:nth-child(1) .svc-icon{background:rgba(6,182,212,.12);color:var(--teal);box-shadow:0 0 22px rgba(6,182,212,.15)}
.svc-card:nth-child(2) .svc-icon{background:rgba(16,185,129,.12);color:var(--green);box-shadow:0 0 22px rgba(16,185,129,.15)}
.svc-card:nth-child(3) .svc-icon{background:rgba(239,68,68,.12);color:var(--red);box-shadow:0 0 22px rgba(239,68,68,.15)}
.svc-card:nth-child(4) .svc-icon{background:rgba(245,158,11,.12);color:var(--gold);box-shadow:0 0 22px rgba(245,158,11,.15)}
.svc-card:nth-child(5) .svc-icon{background:rgba(139,92,246,.12);color:var(--purple);box-shadow:0 0 22px rgba(139,92,246,.15)}
.svc-card:nth-child(6) .svc-icon{background:rgba(59,130,246,.12);color:var(--blue);box-shadow:0 0 22px rgba(59,130,246,.15)}
.svc-card h3{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:800;color:var(--t1);margin-bottom:10px}
.svc-card p{color:var(--t2);font-size:.88rem;line-height:1.7}

/* ---- WHY CHOOSE US ---- */
.why-us{
    padding:120px 7%;
    background:linear-gradient(135deg,#050d1a 0%,#080f1e 100%);
    position:relative;overflow:hidden;
}
.why-us::before{
    content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse 60% 80% at 0% 50%,rgba(6,182,212,.06) 0%,transparent 65%),
               radial-gradient(ellipse 50% 60% at 100% 50%,rgba(139,92,246,.06) 0%,transparent 65%);
}
.why-inner{
    display:grid;grid-template-columns:1fr 1fr;
    gap:80px;align-items:center;max-width:1200px;margin:0 auto;
    position:relative;z-index:1;
}
/* Left — medical visual */
.why-visual{position:relative;display:flex;justify-content:center;align-items:center}
.why-circle{
    width:320px;height:320px;border-radius:50%;
    background:radial-gradient(circle at 35% 35%,rgba(6,182,212,.2) 0%,rgba(6,18,48,.9) 55%);
    border:1px solid rgba(6,182,212,.18);
    display:flex;align-items:center;justify-content:center;
    font-size:7rem;color:rgba(6,182,212,.3);
    box-shadow:0 0 60px rgba(6,182,212,.1),0 0 120px rgba(6,182,212,.05);
    position:relative;z-index:2;
    animation:pulse-ring 4s ease-in-out infinite;
}
@keyframes pulse-ring{0%,100%{box-shadow:0 0 60px rgba(6,182,212,.1),0 0 120px rgba(6,182,212,.05)}50%{box-shadow:0 0 80px rgba(6,182,212,.22),0 0 160px rgba(6,182,212,.1)}}
.why-circle i{animation:heartbeat 1.6s ease-in-out infinite}
/* Orbiting dots */
.why-orbit{
    position:absolute;width:420px;height:420px;border-radius:50%;
    border:1px dashed rgba(6,182,212,.15);
    animation:orbit-spin 20s linear infinite;z-index:1;
}
.why-orbit-dot{
    position:absolute;width:12px;height:12px;border-radius:50%;
    background:var(--teal);top:-6px;left:50%;margin-left:-6px;
    box-shadow:0 0 12px var(--teal),0 0 24px rgba(6,182,212,.5);
}

/* Right — benefits list */
.why-content .section-header{margin-bottom:40px}
.benefit-list{display:flex;flex-direction:column;gap:22px}
.benefit-item{
    display:flex;gap:18px;align-items:flex-start;
    padding:20px 22px;border-radius:16px;
    background:rgba(6,18,48,.5);
    border:1px solid rgba(255,255,255,.06);
    transition:.35s;cursor:default;
}
.benefit-item:hover{background:rgba(6,182,212,.06);border-color:rgba(6,182,212,.2);transform:translateX(6px)}
.benefit-icon{
    width:46px;height:46px;border-radius:13px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:1.2rem;
}
.benefit-item:nth-child(1) .benefit-icon{background:rgba(6,182,212,.12);color:var(--teal)}
.benefit-item:nth-child(2) .benefit-icon{background:rgba(16,185,129,.12);color:var(--green)}
.benefit-item:nth-child(3) .benefit-icon{background:rgba(245,158,11,.12);color:var(--gold)}
.benefit-item:nth-child(4) .benefit-icon{background:rgba(239,68,68,.12);color:var(--red)}
.benefit-text h4{font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--t1);margin-bottom:5px;font-size:.95rem}
.benefit-text p{color:var(--t2);font-size:.84rem;line-height:1.65}

/* ---- PATIENT JOURNEY ---- */
.journey{
    padding:120px 7%;background:var(--navy);position:relative;overflow:hidden;
}
.journey::before{
    content:'';position:absolute;inset:0;
    background-image:linear-gradient(rgba(6,182,212,.04) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(6,182,212,.04) 1px,transparent 1px);
    background-size:50px 50px;
}
.journey-grid{
    display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:30px;position:relative;z-index:1;margin-top:60px;
}
/* Connecting line */
.journey-grid::before{
    content:'';position:absolute;
    top:50px;left:calc(100%/8);right:calc(100%/8);height:2px;
    background:linear-gradient(90deg,var(--teal),var(--blue),var(--green));
    z-index:0;opacity:.25;
}
.journey-step{
    text-align:center;padding:36px 24px;border-radius:22px;
    background:rgba(6,18,48,.7);border:1px solid rgba(6,182,212,.1);
    position:relative;z-index:1;transition:.35s;
}
.journey-step:hover{transform:translateY(-10px);border-color:rgba(6,182,212,.35);box-shadow:var(--glow-teal)}
.step-num{
    width:52px;height:52px;border-radius:50%;margin:0 auto 18px;
    background:linear-gradient(135deg,var(--teal),var(--blue));
    display:flex;align-items:center;justify-content:center;
    font-family:'Space Grotesk',sans-serif;font-size:1.2rem;font-weight:800;color:#fff;
    box-shadow:var(--glow-teal);
}
.journey-step h4{font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--t1);margin-bottom:8px}
.journey-step p{color:var(--t2);font-size:.84rem;line-height:1.6}

/* ---- PORTAL SECTION ---- */
.portals{
    padding:120px 7%;
    background:linear-gradient(135deg,#050d1a 0%,#080f1e 100%);
    text-align:center;position:relative;overflow:hidden;
}
.portals::before{
    content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
    width:900px;height:600px;border-radius:50%;
    background:radial-gradient(ellipse,rgba(6,182,212,.07) 0%,transparent 70%);
}
.portals .section-header{display:inline-block;text-align:center;margin-bottom:60px}
.portals .section-header h2{margin-bottom:12px}
.portals .section-header p{margin:0 auto}

.portal-grid{
    display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:22px;max-width:980px;margin:0 auto;position:relative;z-index:1;
}
.portal-card{
    background:rgba(6,16,42,.8);
    border:1px solid rgba(6,182,212,.1);
    backdrop-filter:blur(28px);
    padding:50px 28px 42px;border-radius:26px;
    cursor:pointer;transition:all .4s cubic-bezier(.175,.885,.32,1.275);
    position:relative;overflow:hidden;
}
.portal-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    opacity:0;transition:.35s;
}
.portal-card:nth-child(1)::before{background:linear-gradient(90deg,var(--teal),var(--blue))}
.portal-card:nth-child(2)::before{background:linear-gradient(90deg,var(--green),var(--teal))}
.portal-card:nth-child(3)::before{background:linear-gradient(90deg,var(--blue),var(--purple))}
.portal-card:nth-child(4)::before{background:linear-gradient(90deg,var(--gold),var(--red))}
.portal-card:hover{transform:translateY(-14px);border-color:rgba(6,182,212,.35);box-shadow:0 28px 70px rgba(0,0,0,.5),var(--glow-teal)}
.portal-card:hover::before{opacity:1}
.portal-ring{
    width:90px;height:90px;border-radius:50%;margin:0 auto 22px;
    display:flex;align-items:center;justify-content:center;
    font-size:2.4rem;border:2px solid rgba(6,182,212,.18);
    transition:.35s;position:relative;
}
.portal-ring::after{
    content:'';position:absolute;inset:-4px;border-radius:50%;
    border:1px solid rgba(6,182,212,.08);
    animation:orbit-spin 8s linear infinite;
}
.portal-card:hover .portal-ring{transform:scale(1.08);border-color:rgba(6,182,212,.45);box-shadow:var(--glow-teal)}
.portal-card:nth-child(1) .portal-ring{background:rgba(6,182,212,.08);color:var(--teal)}
.portal-card:nth-child(2) .portal-ring{background:rgba(16,185,129,.08);color:var(--green);border-color:rgba(16,185,129,.2)}
.portal-card:nth-child(3) .portal-ring{background:rgba(139,92,246,.08);color:var(--purple);border-color:rgba(139,92,246,.2)}
.portal-card:nth-child(4) .portal-ring{background:rgba(245,158,11,.08);color:var(--gold);border-color:rgba(245,158,11,.2)}
.portal-card h3{font-family:'Space Grotesk',sans-serif;font-weight:800;font-size:1.12rem;color:var(--t1);margin-bottom:7px}
.portal-card p{color:var(--t2);font-size:.84rem;line-height:1.55}
.portal-arrow{margin-top:22px;display:flex;align-items:center;justify-content:center;gap:8px;color:rgba(6,182,212,.5);font-size:.78rem;font-weight:800;transition:.3s}
.portal-card:hover .portal-arrow{color:var(--teal);gap:14px;text-shadow:0 0 14px var(--teal)}

/* ---- TESTIMONIALS ---- */
.testimonials{
    padding:120px 7%;background:linear-gradient(180deg,var(--navy2) 0%,var(--navy) 100%);
    position:relative;overflow:hidden;
}
.testimonials-track{
    display:grid;grid-template-columns:repeat(3,1fr);
    gap:24px;max-width:1200px;margin:60px auto 0;
}
.testi-card{
    background:rgba(6,18,48,.8);
    border:1px solid rgba(255,255,255,.07);
    border-radius:22px;padding:36px;
    backdrop-filter:blur(24px);transition:.35s;
    position:relative;overflow:hidden;
}
.testi-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:2px;opacity:0;transition:.35s;
    background:linear-gradient(90deg,var(--teal),var(--blue));
}
.testi-card:hover{transform:translateY(-8px);border-color:rgba(6,182,212,.22);box-shadow:0 24px 60px rgba(0,0,0,.4),var(--glow-teal)}
.testi-card:hover::before{opacity:1}
.testi-stars{color:#fbbf24;font-size:.9rem;margin-bottom:16px;letter-spacing:2px}
.testi-text{
    color:#b0cfe8;font-size:.92rem;line-height:1.8;
    margin-bottom:22px;font-style:italic;
}
.testi-text::before{content:'\201C';font-size:2.5rem;color:rgba(6,182,212,.3);font-style:normal;line-height:1;display:block;margin-bottom:-10px}
.testi-person{display:flex;align-items:center;gap:14px}
.testi-avatar{
    width:48px;height:48px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:1.3rem;font-weight:800;color:#fff;flex-shrink:0;
}
.testi-card:nth-child(1) .testi-avatar{background:linear-gradient(135deg,#0891b2,#06b6d4)}
.testi-card:nth-child(2) .testi-avatar{background:linear-gradient(135deg,#059669,#10b981)}
.testi-card:nth-child(3) .testi-avatar{background:linear-gradient(135deg,#7c3aed,#8b5cf6)}
.testi-name{font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--t1);font-size:.92rem}
.testi-role{color:var(--t3);font-size:.74rem;margin-top:2px}

/* ---- CTA BANNER ---- */
.cta-banner{
    padding:100px 7%;
    background:linear-gradient(135deg,#0a1c3a 0%,#061228 50%,#0a1c3a 100%);
    text-align:center;position:relative;overflow:hidden;
}
.cta-banner::before{
    content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse 80% 100% at 50% 50%,rgba(6,182,212,.1) 0%,transparent 65%);
}
.cta-banner h2{
    font-family:'Space Grotesk',sans-serif;
    font-size:clamp(2rem,4vw,3.2rem);font-weight:800;
    color:var(--t1);letter-spacing:-1.5px;margin-bottom:16px;
    position:relative;z-index:1;
}
.cta-banner p{color:var(--t2);font-size:1.05rem;margin-bottom:40px;position:relative;z-index:1}
.cta-banner .cta-btns{display:flex;gap:18px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1}

/* Red cross large bg decoration */
.cta-cross{
    position:absolute;opacity:.04;right:8%;top:50%;transform:translateY(-50%);
    font-size:14rem;color:var(--teal);
}

/* ---- FOOTER ---- */
footer{
    background:#030810;
    border-top:1px solid rgba(6,182,212,.07);
    padding:80px 7% 32px;
}
.footer-grid{
    display:grid;grid-template-columns:2fr 1fr 1fr 1fr;
    gap:50px;margin-bottom:60px;
}
.footer-brand .logo-text{
    font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:800;
    color:var(--t1);margin-bottom:14px;display:flex;align-items:center;gap:10px;
}
.footer-brand .logo-text i{color:#ef4444}
.footer-brand p{color:var(--t2);font-size:.88rem;line-height:1.8;max-width:280px;margin-bottom:24px}
.footer-social{display:flex;gap:12px}
.social-btn{
    width:38px;height:38px;border-radius:10px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
    display:flex;align-items:center;justify-content:center;
    color:var(--t2);font-size:.9rem;transition:.25s;text-decoration:none;
}
.social-btn:hover{background:rgba(6,182,212,.12);border-color:rgba(6,182,212,.3);color:var(--teal)}
footer h5{color:var(--t1);font-family:'Space Grotesk',sans-serif;font-weight:700;margin-bottom:18px;font-size:.95rem}
footer a{display:block;color:var(--t2);text-decoration:none;font-size:.87rem;margin-bottom:10px;transition:.2s}
footer a:hover{color:var(--teal);padding-left:4px}
.footer-contact-item{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px}
.footer-contact-item i{color:var(--teal);font-size:.85rem;margin-top:3px}
.footer-contact-item span{color:var(--t2);font-size:.87rem;line-height:1.5}
.footer-bottom{
    border-top:1px solid rgba(255,255,255,.05);padding-top:24px;
    display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;
}
.footer-bottom p,.footer-bottom a{color:var(--t3);font-size:.8rem;text-decoration:none}
.footer-bottom a:hover{color:var(--teal)}

/* ---- SCROLL REVEAL ---- */
.reveal{opacity:0;transform:translateY(40px);transition:opacity .75s ease,transform .75s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
.reveal-left{opacity:0;transform:translateX(-40px);transition:opacity .75s ease,transform .75s ease}
.reveal-left.visible{opacity:1;transform:translateX(0)}
.reveal-right{opacity:0;transform:translateX(40px);transition:opacity .75s ease,transform .75s ease}
.reveal-right.visible{opacity:1;transform:translateX(0)}
.rd1{transition-delay:.1s}.rd2{transition-delay:.2s}.rd3{transition-delay:.3s}.rd4{transition-delay:.4s}.rd5{transition-delay:.5s}

/* ---- SCROLL PROGRESS BAR ---- */
#scroll-progress{
    position:fixed;top:0;left:0;height:3px;width:0%;z-index:9999;
    background:linear-gradient(90deg,var(--teal),var(--blue),var(--green));
    transition:width .1s linear;
    box-shadow:0 0 10px rgba(6,182,212,.7);
}

/* =====================================================================
   ██  DASHBOARD  ██
   ===================================================================== */
.dashboard-layout{
    display:grid;grid-template-columns:275px 1fr;min-height:100vh;
}

/* ---- SIDEBAR ---- */
aside{
    background:rgba(3,8,20,.98);
    border-right:1px solid rgba(6,182,212,.1);
    padding:30px 18px;
    display:flex;flex-direction:column;
    position:sticky;top:0;height:100vh;overflow-y:auto;
}
.sb-logo{
    display:flex;align-items:center;gap:12px;
    padding:0 8px 34px;
    border-bottom:1px solid rgba(6,182,212,.08);margin-bottom:22px;
}
.sb-logo-icon{
    width:40px;height:40px;border-radius:11px;
    background:linear-gradient(135deg,#dc2626,#ef4444);
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;color:#fff;
    box-shadow:0 0 18px rgba(239,68,68,.45);
    animation:heart-glow 1.6s ease-in-out infinite alternate;
}
.sb-logo i{animation:heartbeat 1.5s ease-in-out infinite}
.sb-logo-text{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:800;color:var(--t1)}
.sb-logo-sub{color:var(--teal);font-size:.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase}

.menu-item{
    display:flex;align-items:center;gap:12px;
    padding:13px 18px;border-radius:13px;
    cursor:pointer;color:var(--t2);font-weight:600;font-size:.875rem;
    margin-bottom:4px;transition:.25s;border:none;background:none;
    width:100%;text-align:left;font-family:'Plus Jakarta Sans',sans-serif;
    position:relative;overflow:hidden;
}
.menu-item i{width:18px;text-align:center;font-size:.9rem;transition:.25s;flex-shrink:0}
.menu-item:hover{background:rgba(6,182,212,.07);color:var(--teal)}
.menu-item:hover i{color:var(--teal)}
.menu-item.active{
    background:linear-gradient(135deg,rgba(6,182,212,.15),rgba(6,182,212,.06));
    color:var(--teal);border:1px solid rgba(6,182,212,.2);
    box-shadow:0 0 20px rgba(6,182,212,.07);
}
.menu-item.active i{color:var(--teal);filter:drop-shadow(0 0 6px var(--teal))}
.menu-item.active::before{
    content:'';position:absolute;left:0;top:0;bottom:0;width:3px;
    background:var(--teal);border-radius:0 3px 3px 0;
    box-shadow:0 0 12px var(--teal);
}
.sb-footer{
    margin-top:auto;padding-top:20px;
    border-top:1px solid rgba(6,182,212,.07);
}
.sb-system-badge{
    background:rgba(16,185,129,.08);
    border:1px solid rgba(16,185,129,.18);
    border-radius:11px;padding:11px 14px;
    display:flex;align-items:center;gap:9px;
}
.sb-system-badge .dot{width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 8px #34d399;animation:live-pulse 1.8s infinite}
.sb-system-badge span{color:#6ee7b7;font-size:.72rem;font-weight:700}

/* ---- DASHBOARD MAIN AREA ---- */
.dash-main{
    padding:36px 44px;overflow-y:auto;
    background:var(--navy);
    background-image:radial-gradient(ellipse 70% 35% at 85% 0%,rgba(6,182,212,.05) 0%,transparent 60%),
                     radial-gradient(ellipse 50% 35% at 15% 100%,rgba(139,92,246,.04) 0%,transparent 60%);
}
.dash-header{
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:34px;padding-bottom:26px;
    border-bottom:1px solid rgba(6,182,212,.08);
}
.dash-header-left h2{
    font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:800;
    color:var(--t1);letter-spacing:-.5px;
}
.dash-header-right{display:flex;align-items:center;gap:14px}
.user-badge{
    display:flex;align-items:center;gap:11px;
    background:rgba(6,18,48,.9);
    border:1px solid rgba(6,182,212,.15);
    padding:10px 20px;border-radius:40px;
    backdrop-filter:blur(16px);
}
.user-badge i{color:var(--teal);font-size:1.05rem;filter:drop-shadow(0 0 8px var(--teal))}
.user-badge span{font-size:.87rem;font-weight:700;color:var(--t1)}

/* ---- STAT BOXES (overview) ---- */
.stats-grid{
    display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));
    gap:20px;margin-bottom:28px;
}
.stat-box{
    padding:28px;border-radius:18px;position:relative;overflow:hidden;
    border:1px solid rgba(255,255,255,.07);
    transition:transform .3s,box-shadow .3s;
    color:#fff;
}
.stat-box:hover{transform:translateY(-5px)}
.stat-box h4{font-size:.7rem;font-weight:800;opacity:.85;margin-bottom:9px;text-transform:uppercase;letter-spacing:.9px}
.stat-box h2{font-family:'Space Grotesk',sans-serif;font-size:clamp(1.4rem,3.5vw,2.4rem);font-weight:800;letter-spacing:-1.5px}
.stat-box i{position:absolute;right:20px;top:50%;transform:translateY(-50%);font-size:3rem;opacity:.1}
/* Override JS inline backgrounds */
.stat-box[style*="background:var(--primary)"]{
    background:linear-gradient(135deg,#1d5eb8,#2563eb) !important;
    box-shadow:0 8px 32px rgba(37,99,235,.4),inset 0 1px 0 rgba(255,255,255,.08);
}
.stat-box[style*="background:var(--success)"]{
    background:linear-gradient(135deg,#059669,#10b981) !important;
    box-shadow:0 8px 32px rgba(16,185,129,.4),inset 0 1px 0 rgba(255,255,255,.08);
}
.stat-box[style*="background:var(--accent)"]{
    background:linear-gradient(135deg,#d97706,#f59e0b) !important;
    box-shadow:0 8px 32px rgba(245,158,11,.4),inset 0 1px 0 rgba(255,255,255,.08);
}
.stat-box[style*="background:var(--danger)"]{
    background:linear-gradient(135deg,#b91c1c,#ef4444) !important;
    box-shadow:0 8px 32px rgba(239,68,68,.4),inset 0 1px 0 rgba(255,255,255,.08);
}
.stat-box[style*="background:#6d28d9"]{
    background:linear-gradient(135deg,#5b21b6,#7c3aed) !important;
    box-shadow:0 8px 32px rgba(124,58,237,.4),inset 0 1px 0 rgba(255,255,255,.08);
}
.stat-box[style*="background:var(--warn)"]{
    background:linear-gradient(135deg,#b45309,#d97706) !important;
    box-shadow:0 8px 32px rgba(217,119,6,.4),inset 0 1px 0 rgba(255,255,255,.08);
}

/* ---- MOBILE HAMBURGER BUTTON ---- */
.mob-menu-btn{
    display:none;
    align-items:center;justify-content:center;
    width:40px;height:40px;border-radius:10px;
    background:rgba(6,182,212,.12);border:1px solid rgba(6,182,212,.25);
    color:var(--teal);font-size:1.1rem;
    cursor:pointer;flex-shrink:0;transition:.25s;
    margin-right:8px;
}
.mob-menu-btn:hover{background:rgba(6,182,212,.22);}
.sidebar-overlay{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.65);backdrop-filter:blur(4px);
    z-index:200;
}
.sidebar-overlay.open{display:block;}

/* =====================================================================
   RESPONSIVE
   ===================================================================== */
@media(max-width:1100px){
    .footer-grid{grid-template-columns:1fr 1fr}
    .why-inner{grid-template-columns:1fr;gap:60px}
    .why-visual{display:none}
    .testimonials-track{grid-template-columns:1fr}
}
@media(max-width:960px){
    .hero-inner{grid-template-columns:1fr;padding:80px 5% 80px;text-align:center}
    .hero-visual{display:none}
    .hero-sub{max-width:100%}
    .hero-btns,.hero-trust{justify-content:center}
    .dashboard-layout{grid-template-columns:1fr}
    aside{
        display:flex;position:fixed;left:-290px;top:0;
        height:100vh;z-index:201;width:275px;
        transition:left .3s ease;
        box-shadow:4px 0 24px rgba(0,0,0,.5);
    }
    aside.mob-open{left:0;}
    .mob-menu-btn{display:flex;}
    .dash-main{padding:16px 14px;}
    .dash-header{flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-bottom:16px;}
    .dash-header-left h2{font-size:1.2rem;}
    .dash-header-right{gap:8px;}
    .user-badge{padding:8px 12px;}
    .stats-grid{grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;}
    .stat-box{padding:18px 14px;}
    .card{padding:16px;}
}
@media(max-width:640px){
    .form-row.col2,.form-row.col3{grid-template-columns:1fr}
    .footer-grid{grid-template-columns:1fr}
    .testimonials-track{grid-template-columns:1fr}
    .portal-grid{grid-template-columns:1fr 1fr}
    .dash-main{padding:12px 10px;}
    .dash-header-right .btn{padding:8px 12px;font-size:.78rem;}
    .stats-grid{grid-template-columns:1fr 1fr;gap:10px;}
    .stat-box h2{font-size:clamp(1.1rem,6vw,1.8rem)!important;letter-spacing:-.5px;}
    .card div[style*="font-size:1.8rem"]{font-size:clamp(.85rem,4vw,1.4rem)!important;word-break:break-word;}
    .tbl-wrap table{font-size:.78rem;}
    .btn-sm{padding:5px 10px;font-size:.72rem;}
    .form-row{grid-template-columns:1fr!important;}
}

</style>
</head>
<body>
<div id="scroll-progress"></div>

<!-- ================================================================
     LANDING PAGE
     ================================================================ -->
<div id="landingPage" class="page active-page">

    <!-- NAV -->
    <header class="main-nav" id="mainNav">
        <a class="logo" href="#">
            <div class="logo-icon"><i class="fas fa-heart-pulse"></i></div>
            MediCare HMS
        </a>
        <nav class="main-links">
            <a href="#hero">Home</a>
            <a href="#services">Services</a>
            <a href="#portals">Portals</a>
            <a href="#portals" onclick="openLogin('Admin')" style="margin-left:6px" class="btn btn-teal" style="padding:10px 22px">Login</a>
        </nav>
    </header>

    <!-- HERO -->
    <section class="hero" id="hero">
        <!-- Background -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="blob blob-4"></div>
        <div class="medical-cross"></div>
        <div class="hero-particles" id="heroParticles"></div>

        <!-- Orbiting rings around visual -->
        <div class="orb-ring-1 orb-ring" style="position:absolute;top:50%;left:62%;transform:translate(-50%,-50%)"></div>
        <div class="orb-ring-2 orb-ring" style="position:absolute;top:50%;left:62%;transform:translate(-50%,-50%)"></div>
        <div class="orb-ring-3 orb-ring" style="position:absolute;top:50%;left:62%;transform:translate(-50%,-50%)"></div>

        <div class="hero-inner">
            <!-- Left -->
            <div class="hero-content">
                <div class="hero-badge">
                    <div class="live-dot"></div>
                    Hospital Management System — v2.0
                </div>
                <h1>
                    <span class="h1-line1">Compassionate Care,</span>
                    <span class="h1-line2">Powered by Tech</span>
                </h1>
                <p class="hero-sub">
                    A unified platform built for <strong>doctors, patients, staff, and administrators</strong>
                    — where every record, appointment, and decision is at your fingertips.
                </p>
                <div class="hero-btns">
                    <a href="#portals" class="btn btn-teal btn-lg"><i class="fas fa-rocket"></i> Access Portal</a>
                    <a href="#services" class="btn btn-ghost btn-lg"><i class="fas fa-circle-play"></i> See Features</a>
                </div>
                <div class="hero-trust">
                    <div class="trust-item"><i class="fas fa-shield-check"></i> HIPAA Compliant</div>
                    <div class="trust-item"><i class="fas fa-circle-check"></i> 99.9% Uptime</div>
                    <div class="trust-item"><i class="fas fa-lock"></i> Secure & Private</div>
                </div>
            </div>

            <!-- Right — Dashboard card -->
            <div class="hero-visual">
                <!-- Floating chips -->
                <div class="float-chip chip-top-right">
                    <i class="fas fa-arrow-trend-up"></i> Patients +14% Today
                </div>
                <div class="float-chip chip-bottom-left">
                    <i class="fas fa-clock"></i> Wait time: 8 min avg
                </div>
                <div class="float-chip chip-top-left">
                    <i class="fas fa-circle-check" style="color:#34d399"></i> All Systems Live
                </div>

                <div class="hero-dashboard" id="heroCard">
                    <div class="dash-card">
                        <div class="dash-scan"></div>
                        <div class="dc-header">
                            <div class="dc-logo-sm"><i class="fas fa-heart-pulse"></i></div>
                            <div>
                                <div class="dc-title">MediCare HMS</div>
                                <div class="dc-sub">Live Clinical Dashboard</div>
                            </div>
                        </div>
                        <div class="dc-big-metric">
                            <div class="dc-ml">Active Appointments Today</div>
                            <div class="dc-mv">1,248 <span class="dc-mt"><i class="fas fa-arrow-trend-up"></i> 14%</span></div>
                        </div>
                        <div class="dc-mini-row">
                            <div class="dc-mini"><div class="v">50+</div><div class="l">Specialties</div></div>
                            <div class="dc-mini"><div class="v">24/7</div><div class="l">Emergency</div></div>
                            <div class="dc-mini"><div class="v">99%</div><div class="l">Success Rate</div></div>
                            <div class="dc-mini"><div class="v">0 sec</div><div class="l">Record Access</div></div>
                        </div>
                        <div class="dc-vitals">
                            <div class="vital-dot"></div>
                            <div class="vital-label">All Vitals Normal</div>
                            <div class="vital-ecg">
                                <svg viewBox="0 0 140 30" fill="none">
                                    <polyline points="0,15 12,15 18,3 24,27 30,15 42,15 48,7 54,23 60,15 72,15 78,1 84,29 90,15 102,15 108,9 114,21 120,15 132,15 140,15"
                                        stroke="#06b6d4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity=".9"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full-width ECG strip at bottom of hero -->
        <div class="ecg-strip">
            <svg viewBox="0 0 1440 70" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <polyline
                    points="0,35 60,35 80,35 90,10 100,60 110,35 140,35 160,35 175,20 185,50 195,35 240,35 260,35 270,5 280,65 290,35 320,35 360,35 380,20 395,50 405,35 450,35 480,35 490,12 500,58 510,35 540,35 580,35 595,18 607,52 617,35 660,35 700,35 710,8 720,62 730,35 760,35 800,35 815,22 825,48 835,35 880,35 920,35 930,6 940,64 950,35 980,35 1020,35 1035,16 1047,54 1057,35 1100,35 1140,35 1150,10 1162,60 1172,35 1200,35 1240,35 1255,20 1265,50 1275,35 1320,35 1360,35 1375,14 1387,56 1397,35 1440,35"
                    stroke="rgba(6,182,212,0.5)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline
                    points="0,35 60,35 80,35 90,10 100,60 110,35 140,35 160,35 175,20 185,50 195,35 240,35 260,35 270,5 280,65 290,35 320,35 360,35 380,20 395,50 405,35 450,35 480,35 490,12 500,58 510,35 540,35 580,35 595,18 607,52 617,35 660,35 700,35 710,8 720,62 730,35 760,35 800,35 815,22 825,48 835,35 880,35 920,35 930,6 940,64 950,35 980,35 1020,35 1035,16 1047,54 1057,35 1100,35 1140,35 1150,10 1162,60 1172,35 1200,35 1240,35 1255,20 1265,50 1275,35 1320,35 1360,35 1375,14 1387,56 1397,35 1440,35"
                    stroke="rgba(6,182,212,0.5)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" transform="translate(1440,0)"/>
            </svg>
        </div>
    </section>

    <!-- TICKER -->
    <div class="ticker-wrap">
        <div class="ticker-track" id="tickerTrack">
            <div class="ticker-item"><i class="fas fa-heart-pulse"></i> 15,000+ Patients Treated Daily</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-user-doctor"></i> 200+ Certified Specialists</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-bed-pulse"></i> 99.9% System Uptime</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-shield-halved"></i> HIPAA Compliant & Secure</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-clock"></i> 24/7 Emergency Response</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-dna"></i> Cutting-Edge Medical Tech</div>
            <div class="ticker-item ticker-sep">·</div>
            <!-- Duplicate for seamless loop -->
            <div class="ticker-item"><i class="fas fa-heart-pulse"></i> 15,000+ Patients Treated Daily</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-user-doctor"></i> 200+ Certified Specialists</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-bed-pulse"></i> 99.9% System Uptime</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-shield-halved"></i> HIPAA Compliant & Secure</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-clock"></i> 24/7 Emergency Response</div>
            <div class="ticker-item ticker-sep">·</div>
            <div class="ticker-item"><i class="fas fa-dna"></i> Cutting-Edge Medical Tech</div>
            <div class="ticker-item ticker-sep">·</div>
        </div>
    </div>

    <!-- ANIMATED STATS COUNTERS -->
    <section class="stats-section" id="statsSection">
        <div class="stats-inner">
            <div class="stat-counter-card reveal">
                <div class="stat-counter-icon"><i class="fas fa-user-doctor"></i></div>
                <div class="counter-number" data-target="200" data-suffix="+">0</div>
                <div class="counter-label">Medical Specialists</div>
                <div class="counter-sub">Across all departments</div>
            </div>
            <div class="stat-counter-card reveal rd1">
                <div class="stat-counter-icon"><i class="fas fa-users"></i></div>
                <div class="counter-number" data-target="15000" data-suffix="+">0</div>
                <div class="counter-label">Patients Served</div>
                <div class="counter-sub">Monthly average</div>
            </div>
            <div class="stat-counter-card reveal rd2">
                <div class="stat-counter-icon"><i class="fas fa-heart-pulse"></i></div>
                <div class="counter-number" data-target="99" data-suffix="%">0</div>
                <div class="counter-label">Patient Satisfaction</div>
                <div class="counter-sub">Verified reviews</div>
            </div>
            <div class="stat-counter-card reveal rd3">
                <div class="stat-counter-icon"><i class="fas fa-building-columns"></i></div>
                <div class="counter-number" data-target="50" data-suffix="+">0</div>
                <div class="counter-label">Specialties</div>
                <div class="counter-sub">Full medical spectrum</div>
            </div>
            <div class="stat-counter-card reveal rd4">
                <div class="stat-counter-icon"><i class="fas fa-clock-rotate-left"></i></div>
                <div class="counter-number" data-target="24" data-suffix="/7">0</div>
                <div class="counter-label">Emergency Care</div>
                <div class="counter-sub">Always available</div>
            </div>
            <div class="stat-counter-card reveal rd5">
                <div class="stat-counter-icon"><i class="fas fa-star"></i></div>
                <div class="counter-number" data-target="12" data-suffix=" Yrs">0</div>
                <div class="counter-label">Years of Excellence</div>
                <div class="counter-sub">Trusted by thousands</div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section class="services" id="services">
        <div class="section-header">
            <div class="eyebrow reveal">Our Capabilities</div>
            <h2 class="reveal">Everything Your Hospital Needs</h2>
            <p class="reveal">One unified platform that handles every aspect of modern hospital operations — intelligently.</p>
        </div>
        <div class="services-grid">
            <div class="svc-card reveal">
                <div class="svc-icon"><i class="fas fa-file-medical"></i></div>
                <h3>Smart EHR</h3>
                <p>Complete digital health records with instant access, AI-assisted documentation, and full history tracking.</p>
            </div>
            <div class="svc-card reveal rd1">
                <div class="svc-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Appointment Management</h3>
                <p>Book, manage, and track appointments seamlessly across all departments and doctors in real-time.</p>
            </div>
            <div class="svc-card reveal rd2">
                <div class="svc-icon"><i class="fas fa-bed-pulse"></i></div>
                <h3>Room &amp; Ward Control</h3>
                <p>Live bed availability, smart room allocation, and patient ward assignments — all at a glance.</p>
            </div>
            <div class="svc-card reveal rd3">
                <div class="svc-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3>Billing &amp; Revenue</h3>
                <p>Automated invoicing, payment tracking, and financial reports. No missed billings, no confusion.</p>
            </div>
            <div class="svc-card reveal rd4">
                <div class="svc-icon"><i class="fas fa-pills"></i></div>
                <h3>Prescriptions</h3>
                <p>Digital prescriptions written and managed by doctors, instantly accessible by patients and pharmacies.</p>
            </div>
            <div class="svc-card reveal rd5">
                <div class="svc-icon"><i class="fas fa-lock-open"></i></div>
                <h3>Access &amp; Permissions</h3>
                <p>Granular role-based access control. Right permissions to the right people — nothing more, nothing less.</p>
            </div>
        </div>
    </section>

    <!-- WHY CHOOSE US -->
    <section class="why-us">
        <div class="why-inner">
            <div class="why-visual reveal-left">
                <div class="why-orbit">
                    <div class="why-orbit-dot"></div>
                </div>
                <div class="why-circle">
                    <i class="fas fa-heart-pulse"></i>
                </div>
            </div>
            <div class="why-content">
                <div class="section-header reveal-right">
                    <div class="eyebrow">Why Choose Us</div>
                    <h2>Built with Compassion,<br>Designed for Care</h2>
                    <p>We don't just manage records — we protect lives, reduce wait times, and give every patient the attention they deserve.</p>
                </div>
                <div class="benefit-list">
                    <div class="benefit-item reveal-right rd1">
                        <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
                        <div class="benefit-text">
                            <h4>Real-Time — Every Second Counts</h4>
                            <p>Instant updates across all modules. When a patient's condition changes, every department knows immediately.</p>
                        </div>
                    </div>
                    <div class="benefit-item reveal-right rd2">
                        <div class="benefit-icon"><i class="fas fa-shield-heart"></i></div>
                        <div class="benefit-text">
                            <h4>Patient Data Protection</h4>
                            <p>Bank-level encryption and HIPAA compliance. Patient privacy is not a feature — it's a foundation.</p>
                        </div>
                    </div>
                    <div class="benefit-item reveal-right rd3">
                        <div class="benefit-icon"><i class="fas fa-users-gear"></i></div>
                        <div class="benefit-text">
                            <h4>Multi-Role Smart Workflows</h4>
                            <p>Each role — Admin, Doctor, Patient, Receptionist — gets a tailored dashboard built for their exact needs.</p>
                        </div>
                    </div>
                    <div class="benefit-item reveal-right rd4">
                        <div class="benefit-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="benefit-text">
                            <h4>Data-Driven Insights</h4>
                            <p>Full hospital reports, revenue analytics, and occupancy insights to drive smarter decisions daily.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS — PATIENT JOURNEY -->
    <section class="journey" id="journey">
        <div class="section-header" style="text-align:center">
            <div class="eyebrow reveal">Patient Journey</div>
            <h2 class="reveal">From Arrival to Recovery</h2>
            <p class="reveal" style="max-width:480px;margin:0 auto">Every step of your care is tracked, documented, and managed with precision and compassion.</p>
        </div>
        <div class="journey-grid">
            <div class="journey-step reveal">
                <div class="step-num">1</div>
                <h4>Registration</h4>
                <p>Patient registers in seconds. Digital profile created with full medical history support.</p>
            </div>
            <div class="journey-step reveal rd1">
                <div class="step-num">2</div>
                <h4>Book Appointment</h4>
                <p>Choose a specialist, pick a time — instant confirmation with auto fee display.</p>
            </div>
            <div class="journey-step reveal rd2">
                <div class="step-num">3</div>
                <h4>Consultation</h4>
                <p>Doctor reviews records, adds diagnosis, and writes a digital prescription in real-time.</p>
            </div>
            <div class="journey-step reveal rd3">
                <div class="step-num">4</div>
                <h4>Room &amp; Care</h4>
                <p>If admitted, rooms are allocated instantly. Staff are notified automatically.</p>
            </div>
            <div class="journey-step reveal rd4">
                <div class="step-num">5</div>
                <h4>Billing &amp; Discharge</h4>
                <p>Clear, itemized billing generated instantly. Payment tracked with full history retained.</p>
            </div>
        </div>
    </section>

    <!-- LOGIN PORTALS -->
    <section class="portals" id="portals">
        <div class="section-header reveal" style="text-align:center">
            <div class="eyebrow" style="display:inline-block">Access Portals</div>
            <h2>Choose Your Dashboard</h2>
            <p style="max-width:480px;margin:0 auto">Select your role to log into your dedicated, personalized workspace</p>
        </div>
        <div class="portal-grid">
            <div class="portal-card reveal" onclick="openLogin('Admin')">
                <div class="portal-ring"><i class="fas fa-shield-halved"></i></div>
                <h3>Administrator</h3>
                <p>Full system control — staff, departments, reports, access management</p>
                <div class="portal-arrow">Enter Portal <i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="portal-card reveal rd1" onclick="openLogin('Doctor')">
                <div class="portal-ring"><i class="fas fa-user-doctor"></i></div>
                <h3>Doctor</h3>
                <p>Manage your patients, view history, write prescriptions</p>
                <div class="portal-arrow">Enter Portal <i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="portal-card reveal rd2" onclick="openLogin('Patient')">
                <div class="portal-ring"><i class="fas fa-bed-pulse"></i></div>
                <h3>Patient</h3>
                <p>Book appointments, view records, check prescriptions &amp; bills</p>
                <div class="portal-arrow">Enter Portal <i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="portal-card reveal rd3" onclick="openLogin('Receptionist')">
                <div class="portal-ring"><i class="fas fa-bell-concierge"></i></div>
                <h3>Receptionist</h3>
                <p>Register patients, manage rooms, appointments &amp; billing</p>
                <div class="portal-arrow">Enter Portal <i class="fas fa-arrow-right"></i></div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="testimonials">
        <div class="section-header" style="text-align:center">
            <div class="eyebrow reveal">Real Stories</div>
            <h2 class="reveal">Lives Changed, Stories Told</h2>
            <p class="reveal" style="max-width:480px;margin:0 auto">Hear from the patients and doctors whose care was transformed by this system.</p>
        </div>
        <div class="testimonials-track">
            <div class="testi-card reveal">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">The appointment booking was instant and the doctor had my full history ready before I even sat down. I felt truly cared for — not just processed.</p>
                <div class="testi-person">
                    <div class="testi-avatar">A</div>
                    <div>
                        <div class="testi-name">Ayesha Malik</div>
                        <div class="testi-role">Patient — Cardiology Department</div>
                    </div>
                </div>
            </div>
            <div class="testi-card reveal rd1">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">As a doctor, having all patient records organized and instantly searchable saves me at least 2 hours per day. This system genuinely lets me focus on medicine.</p>
                <div class="testi-person">
                    <div class="testi-avatar">R</div>
                    <div>
                        <div class="testi-name">Dr. Raza Mahmood</div>
                        <div class="testi-role">Senior Physician — General Medicine</div>
                    </div>
                </div>
            </div>
            <div class="testi-card reveal rd2">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">Managing billing used to take our team hours. Now it's automated, accurate, and patients can see exactly what they're paying for. Disputes dropped to zero.</p>
                <div class="testi-person">
                    <div class="testi-avatar">S</div>
                    <div>
                        <div class="testi-name">Sara Hassan</div>
                        <div class="testi-role">Head Receptionist — Administration</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="cta-banner">
        <div class="cta-cross"><i class="fas fa-plus"></i></div>
        <h2 class="reveal">Ready to Transform Your Hospital?</h2>
        <p class="reveal">Join thousands of healthcare professionals who trust MediCare HMS every day</p>
        <div class="cta-btns reveal">
            <a href="#portals" class="btn btn-teal btn-lg"><i class="fas fa-rocket"></i> Get Started Now</a>
            <a href="#services" class="btn btn-ghost btn-lg"><i class="fas fa-book-medical"></i> Learn More</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo-text"><i class="fas fa-heart-pulse"></i> MediCare HMS</div>
                <p>A compassionate, technology-driven Hospital Management System designed to put patients first and empower every healthcare professional.</p>
                <div class="footer-social">
                    <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-github"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div>
                <h5>Quick Links</h5>
                <a href="#hero">Home</a>
                <a href="#services">Services</a>
                <a href="#portals">Portals</a>
                <a href="#journey">How It Works</a>
            </div>
            <div>
                <h5>Roles</h5>
                <a href="#" onclick="openLogin('Admin')">Admin Portal</a>
                <a href="#" onclick="openLogin('Doctor')">Doctor Portal</a>
                <a href="#" onclick="openLogin('Patient')">Patient Portal</a>
                <a href="#" onclick="openLogin('Receptionist')">Reception Portal</a>
            </div>
            <div>
                <h5>Contact</h5>
                <div class="footer-contact-item"><i class="fas fa-phone"></i><span>+1 646 663 8030</span></div>
                <div class="footer-contact-item"><i class="fas fa-envelope"></i><span>support@medicarehms.com</span></div>
                <div class="footer-contact-item"><i class="fas fa-location-dot"></i><span>Medical District, City Center, Pakistan</span></div>
                <div class="footer-contact-item"><i class="fas fa-clock"></i><span>Mon–Fri, 9AM–6PM PKT</span></div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 MediCare HMS. All rights reserved. Built with <i class="fas fa-heart" style="color:#ef4444"></i> for healthcare.</p>
            <div style="display:flex;gap:20px">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">HIPAA Compliance</a>
            </div>
        </div>
    </footer>

    <!-- ==================== LANDING JS ==================== -->
    <script>
    (function(){
        // Scroll progress bar
        const bar = document.getElementById('scroll-progress');
        window.addEventListener('scroll', function(){
            const s = document.documentElement.scrollTop;
            const h = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            bar.style.width = (s/h*100) + '%';
        });

        // Floating medical particles
        const icons = ['fa-heart-pulse','fa-stethoscope','fa-syringe','fa-pills','fa-dna','fa-microscope','fa-hospital','fa-user-doctor','fa-notes-medical','fa-vials'];
        const pc = document.getElementById('heroParticles');
        if(pc){
            for(let i=0;i<22;i++){
                const el = document.createElement('i');
                el.className = 'fas ' + icons[i%icons.length] + ' mp';
                el.style.cssText = 'left:'+Math.random()*100+'%;top:'+Math.random()*100+'%;font-size:'+(0.7+Math.random()*1.1)+'rem;animation-delay:'+(Math.random()*7)+'s;animation-duration:'+(5+Math.random()*5)+'s';
                pc.appendChild(el);
            }
        }

        // 3D card mouse tilt
        const card = document.getElementById('heroCard');
        if(card){
            const p = card.parentElement;
            p.addEventListener('mousemove', function(e){
                const r = p.getBoundingClientRect();
                const x = (e.clientX-r.left)/r.width - .5;
                const y = (e.clientY-r.top)/r.height - .5;
                card.style.cssText='animation:none;transform:rotateY('+(x*24)+'deg) rotateX('+(-y*16)+'deg)';
            });
            p.addEventListener('mouseleave', function(){ card.style.cssText=''; });
        }

        // Animated counters
        function animateCounter(el){
            const target = parseInt(el.getAttribute('data-target'));
            const suffix = el.getAttribute('data-suffix')||'';
            const dur = 1800;
            const step = dur/60;
            let current = 0;
            const inc = target/60;
            const timer = setInterval(function(){
                current = Math.min(current+inc, target);
                el.textContent = Math.floor(current).toLocaleString() + suffix;
                if(current >= target) clearInterval(timer);
            }, step);
        }

        // Scroll reveal + counter trigger
        const reveals = document.querySelectorAll('.reveal,.reveal-left,.reveal-right');
        const counters = document.querySelectorAll('.counter-number');
        const countersTriggered = new Set();
        const obs = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if(entry.isIntersecting){
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, {threshold:0.12});
        reveals.forEach(function(el){obs.observe(el)});

        const cObs = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if(entry.isIntersecting && !countersTriggered.has(entry.target)){
                    countersTriggered.add(entry.target);
                    animateCounter(entry.target);
                }
            });
        }, {threshold:0.3});
        counters.forEach(function(el){cObs.observe(el)});

    })();
    </script>

</div><!-- end #landingPage -->

<!-- ================================================================
     LOGIN MODAL
     ================================================================ -->
<div id="loginModal" class="modal-overlay">
    <div class="modal-box">
        <h2 id="loginTitle"><i class="fas fa-lock" style="color:var(--teal)"></i> Secure Login</h2>
        <div id="loginAlert"></div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" id="username" placeholder="Enter your full name" autocomplete="username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="password" placeholder="Your password" autocomplete="current-password">
        </div>
        <button class="btn btn-teal" style="width:100%;justify-content:center;margin-bottom:12px;" onclick="handleLogin()">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
        <button class="btn btn-ghost" style="width:100%;justify-content:center;" onclick="closeLogin()">
            <i class="fas fa-times"></i> Cancel
        </button>
        <div style="margin-top:18px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center">
            <p style="color:var(--t3);font-size:.78rem"><i class="fas fa-shield-check" style="color:var(--green);margin-right:6px"></i>256-bit encrypted &amp; HIPAA compliant</p>
        </div>
    </div>
</div>

<!-- ================================================================
     DASHBOARD
     ================================================================ -->
<div id="dashboardPage" class="page">
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <aside>
            <div class="sb-logo">
                <div class="sb-logo-icon"><i class="fas fa-heart-pulse"></i></div>
                <div>
                    <div class="sb-logo-text">MediCare</div>
                    <div class="sb-logo-sub">HMS Elite</div>
                </div>
            </div>
            <div id="sidebarMenu"></div>
            <div class="sb-footer">
                <div class="sb-system-badge">
                    <div class="dot"></div>
                    <span>All Systems Operational</span>
                </div>
            </div>
        </aside>
        <main class="dash-main">
            <div class="dash-header">
                <div class="dash-header-left">
                    <h2 id="viewTitle">Overview</h2>
                </div>
                <div class="dash-header-right">
                    <button class="mob-menu-btn" onclick="toggleSidebar()" title="Toggle Menu"><i class="fas fa-bars"></i></button>
                    <div class="user-badge">
                        <i class="fas fa-user-circle"></i>
                        <span id="userNameDisplay">Welcome</span>
                    </div>
                    <button class="btn btn-red" onclick="logout()" style="padding:10px 18px">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
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
let currentView = '';

// Cached data
let cache = {
    departments:[], doctors:[], patients:[],
    appointments:[], rooms:[], bills:[], overview:{}
};
function toggleSidebar() {
    document.querySelector('aside').classList.toggle('mob-open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebarOnMobile() {
    if (window.innerWidth <= 960) {
        document.querySelector('aside').classList.remove('mob-open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }
}


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
    const response = await fetch(url, opts);
    const res = await response.json();
    
    // GLOBAL PERMISSION INTERCEPTOR
    // If the server explicitly denies access, throw an alert and halt the script.
    if (res.error && res.error.includes('Access Denied')) {
        alert("🛑 " + res.error);
        throw new Error("Permission Blocked by Server"); // Stops the screen from reloading
    }
    
    return res;
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
function calcAge(dobString) {
    if (!dobString) return '—';
    const diff = Date.now() - new Date(dobString).getTime();
    return Math.abs(new Date(diff).getUTCFullYear() - 1970);
}
function formatAddress(p) {
    let arr = [p.Street, p.City, p.ZipCode].filter(Boolean);
    return arr.length ? arr.join(', ') : '—';
}

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
    Admin: [{icon:'fa-gauge',label:'Overview'},{icon:'fa-building',label:'Departments'},{icon:'fa-user-doctor',label:'Staff Management'},{icon:'fa-user-tie',label:'Receptionists'},{icon:'fa-calendar-check',label:'Appointments'},{icon:'fa-bed',label:'Room Management'},{icon:'fa-chart-bar',label:'Full Report'},{icon:'fa-key',label:'Password Reset'},{icon:'fa-lock-open',label:'Access Control'}],
    Doctor:       [{icon:'fa-stethoscope',label:'My Patients'},{icon:'fa-person-injured',label:'Patient History'}],
    Patient:      [{icon:'fa-calendar-plus',label:'Book Appointment'},{icon:'fa-calendar-alt',label:'My Appointments'},{icon:'fa-notes-medical',label:'My Records'},{icon:'fa-pills',label:'My Prescriptions'}],
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
    currentView = view;
    closeSidebarOnMobile();
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
        'Book Appointment': renderBookAppointmentPatient,
        'My Records':       renderMyRecords,
        'Register Patient': renderRegisterPatient,
        'Room Allocate':    renderRoomAllocate,
        'Billing':          renderBilling,
        'My Prescriptions': renderMyPrescriptions,
        'Write Prescription': renderWritePrescription,
        'Receptionists':    renderReceptionists,
        'Password Reset':   renderPasswordReset,
        'Access Control':   renderAccessControl,
    }[view];
    if (fn) fn(el);
    else el.innerHTML = `<div class="card"><h3>${view} — Coming Soon</h3></div>`;
}


async function refreshCurrentView() {
    const el = document.getElementById('mainContent');
    await fetchAll();
    const fn = {
        'Overview':           renderOverview,
        'Departments':        renderDepts,
        'Staff Management':   renderStaff,
        'Appointments':       renderAppointments,
        'Room Management':    renderRooms,
        'Full Report':        renderReport,
        'My Patients':        renderDoctorPatients,
        'Patient History':    renderDoctorHistory,
        'My Appointments':    renderMyAppointments,
        'Book Appointment':   renderBookAppointmentPatient,
        'My Records':         renderMyRecords,
        'Register Patient':   renderRegisterPatient,
        'Room Allocate':      renderRoomAllocate,
        'Billing':            renderBilling,
        'My Prescriptions':   renderMyPrescriptions,
        'Write Prescription': renderWritePrescription,
        'Receptionists':      renderReceptionists,
        'Password Reset':     renderPasswordReset,
        'Access Control':     renderAccessControl,
    }[currentView];
    if (fn) fn(el);
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
            <div class="form-group" style="margin:0"><label>Date of Birth</label><input type="date" id="patDob"></div>
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
        <div class="form-group" style="margin-top:10px;"><label>Street Address</label><input type="text" id="patStreet" placeholder="123 Main St"></div>
        <div class="form-row col3" style="margin-top:10px">
            <div class="form-group" style="margin:0"><label>City</label><input type="text" id="patCity" placeholder="City"></div>
            <div class="form-group" style="margin:0"><label>Zip Code</label><input type="text" id="patZip" placeholder="Zip"></div>
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
            <td>${calcAge(p.DateOfBirth)}</td>
            <td>${p.Gender==='M'?'Male':p.Gender==='F'?'Female':p.Gender==='O'?'Other':'—'}</td>
            <td>${p.Phone||'—'}</td>
            <td>${formatAddress(p)}</td>
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
    const name    = document.getElementById('patName').value.trim();
    const dob     = document.getElementById('patDob').value;
    const gender  = document.getElementById('patGender').value;
    const phone   = document.getElementById('patPhone').value.trim();
    const street  = document.getElementById('patStreet').value.trim();
    const city    = document.getElementById('patCity').value.trim();
    const zipcode = document.getElementById('patZip').value.trim();
    const pass    = document.getElementById('patPass').value;

    if (!name) { document.getElementById('patAlert').innerHTML=`<div class="alert alert-error">Enter Patient Name</div>`; return; }
    if (pass.length < 6 || pass.length > 15) { document.getElementById('patAlert').innerHTML=`<div class="alert alert-error">Password must be 6-15 chars</div>`; return; }

    const res = await api('add_patient', {name, dob, gender, phone, street, city, zipcode, password:pass});
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
function renderBookAppointmentPatient(el) {
    const docOpts = cache.doctors.map(d => `<option value="${d.id}" data-fee="${d.appointment_fee||0}">${d.name} — ${d.specialization||'General'} (${d.dept_name||'—'})</option>`).join('');
    el.innerHTML = `
        <div class="card">
            <h3><i class="fas fa-calendar-plus" style="color:var(--primary)"></i> Book an Appointment</h3>
            <p style="color:var(--muted);font-size:.9rem;margin-bottom:20px;">You are booking an appointment for yourself. Select a doctor and preferred date below.</p>
            <div id="patBookAlert"></div>

            <div class="form-row col2">
                <div class="form-group">
                    <label>Patient</label>
                    <input type="text" value="${session.user}" disabled style="background:#f0f4fb;color:#aaa;">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="patBookDate" value="${today()}">
                </div>
            </div>

            <div class="form-row col2">
                <div class="form-group">
                    <label>Select Doctor</label>
                    <select id="patBookDoctor" onchange="patUpdateFee()">
                        <option value="">— Choose a Doctor —</option>
                        ${docOpts}
                    </select>
                </div>
                <div class="form-group">
                    <label>Appointment Fee (₨)</label>
                    <input type="text" id="patBookFeeDisplay" disabled placeholder="Select a doctor...">
                </div>
            </div>

            <div class="form-group">
                <label>Reason / Notes</label>
                <textarea id="patBookDiag" placeholder="Describe your symptoms or reason for visit..."></textarea>
            </div>

            <button class="btn btn-blue btn-lg" onclick="submitPatientBooking()">
                <i class="fas fa-calendar-check"></i> Confirm Booking
            </button>
        </div>`;
}

function patUpdateFee() {
    const sel = document.getElementById('patBookDoctor');
    const fee = sel.options[sel.selectedIndex]?.dataset?.fee;
    const display = document.getElementById('patBookFeeDisplay');
    if (display) display.value = fee > 0 ? '₨' + Number(fee).toLocaleString() : 'Free / Not set';
}

async function submitPatientBooking() {
    const date  = document.getElementById('patBookDate').value;
    const docId = document.getElementById('patBookDoctor').value;
    const diag  = document.getElementById('patBookDiag').value.trim();
    const alert = document.getElementById('patBookAlert');
    if (!docId) { alert.innerHTML = `<div class="alert alert-error">Please select a doctor.</div>`; return; }
    const res = await api('add_appointment', {date, patient_id: session.ref_id, doctor_id: docId, diagnosis: diag, status: 'Scheduled'});
    if (res.error) { alert.innerHTML = `<div class="alert alert-error">${res.error}</div>`; return; }
    alert.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> Appointment booked successfully!</div>`;
    document.getElementById('patBookDoctor').value = '';
    document.getElementById('patBookFeeDisplay').value = '';
    document.getElementById('patBookDiag').value = '';
    await fetchAll();
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
    navigate(session.role==='Patient' ? 'My Appointments' : 'Appointments');
}
async function updateApptStatus(id, curStatus, curDiag) {
    const s = prompt("Update status: Scheduled / Pending / Treated / Cancelled", curStatus);
    if (s && ['Scheduled','Pending','Treated','Cancelled'].includes(s)) {
        const diag = prompt("Update diagnosis/notes:", curDiag) ?? curDiag;
        await api('update_appointment', {id, status:s, diagnosis:diag});
        await refreshCurrentView();
    }
}
async function deleteAppt(id) {
    if (!confirm(`Delete appointment #${id}?`)) return;
    await api('delete_appointment', {id});
    await refreshCurrentView();
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
    await refreshCurrentView();
}
async function markAvailable(id) {
    await api('mark_available', {id});
    await refreshCurrentView();
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
    document.getElementById('billAlert').innerHTML=`<div class="alert alert-success">Bill generated successfully!</div>`;
    // Refresh bills cache then switch to list view — no full navigate()
    const freshBills = await api('get_bills','','GET');
    if (!freshBills.error) cache.bills = freshBills;
    toggleBillingView('list');
    const container = document.getElementById('billingTableContainer');
    if (container) container.innerHTML = billsTableHTML();
}
async function changeBillStatus(id, newStatus) {
    const res = await api('update_bill_status', {id, status:newStatus});
    if (res.error) { alert(res.error); return; }
    // Update just this bill in the cache
    const b = cache.bills.find(x => x.BillID == id);
    if (b) b.PaymentStatus = newStatus;
    const container = document.getElementById('billingTableContainer');
    if (container) container.innerHTML = billsTableHTML();
    filterBills(); // Re-apply any active search/filter
}
async function deleteBill(id) {
    if (!confirm(`Delete Bill #${id}?`)) return;
    const res = await api('delete_bill', {id});
    if (res.error) { alert(res.error); return; }
    // Remove from cache and re-render in place
    cache.bills = cache.bills.filter(b => b.BillID != id);
    const container = document.getElementById('billingTableContainer');
    if (container) container.innerHTML = billsTableHTML();
    filterBills();
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
                <div id="medDropdown" style="display:none;position:absolute;top:70px;left:0;width:100%;background:#0f2040;border:1.5px solid rgba(6,182,212,.35);border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.7);z-index:100;max-height:200px;overflow-y:auto;"></div>
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
        drop.innerHTML = `<div style="padding:12px 14px;color:#94b4d0;">No medicines found matching "${q}".</div>`;
    } else {
        res.forEach(m => {
            const div = document.createElement('div');
            div.style.cssText = "padding:10px 14px;border-bottom:1px solid rgba(6,182,212,.18);cursor:pointer;color:#f0f8ff;transition:background .2s;";
            div.innerHTML = `<b style="color:#e2f0ff;">${m.name}</b> <span style="font-size:12px;color:#06b6d4;">(${m.category})</span>`;
            div.onmouseenter = () => div.style.background = 'rgba(6,182,212,.15)';
            div.onmouseleave = () => div.style.background = 'transparent';
            div.onclick = () => addMedToRx(m);
            drop.appendChild(div);
        });
    }
    
    // Always append an "Add New" button at the bottom of the dropdown
    const addNewBtn = document.createElement('div');
    addNewBtn.style.cssText = "padding:10px 14px;background:rgba(59,130,246,.18);cursor:pointer;color:#60a5fa;font-weight:bold;text-align:center;border-bottom-left-radius:8px;border-bottom-right-radius:8px;border-top:1px solid rgba(6,182,212,.25);";
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
        <div style="border:1.5px solid rgba(6,182,212,.25);border-radius:10px;padding:16px;margin-bottom:16px;background:rgba(10,22,50,.85);backdrop-filter:blur(8px);">
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(6,182,212,.18);padding-bottom:10px;margin-bottom:10px;">
                <div><b style="color:#60a5fa;">Dr. ${rx.doctor_name}</b><br><span style="font-size:12px;color:#94b4d0;">Diagnosis: ${rx.Diagnosis || 'N/A'}</span></div>
                <div style="text-align:right"><span class="pill pill-blue">${rx.date}</span><br><span style="font-size:12px;color:#94b4d0;">Rx #${rx.rx_id}</span></div>
            </div>
            <div class="tbl-wrap" style="margin-bottom:10px;">
                <table style="font-size:0.85rem">
                    <tr style="background:rgba(6,182,212,.1);"><th style="padding:8px;color:#e2f0ff;">Medicine</th><th style="padding:8px;color:#e2f0ff;">Dosage</th></tr>
                    ${rx.medicines.map(m => `<tr><td style="padding:8px;color:#f0f8ff;"><b>${m.name}</b></td><td style="padding:8px;color:#cbd5e1;">${m.dosage}</td></tr>`).join('')}
                </table>
            </div>
            ${rx.instructions ? `<div style="background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);padding:10px;border-radius:6px;font-size:0.85rem;color:#e2f0ff;"><b style="color:#60a5fa;">Instructions:</b> ${rx.instructions}</div>` : ''}
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
            <h3><i class="fas fa-user-injured"></i> ${p?p.Name:'Unknown Patient'} ${p?`<span class="pill pill-blue">${p.Gender==='M'?'Male':'Female'}, ${calcAge(p.DateOfBirth)} yrs</span>`:''}</h3>
            ${p?`<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-bottom:16px;font-size:.88rem;">
                <div><span style="color:var(--muted)">Phone:</span> ${p.Phone||'—'}</div>
                <div><span style="color:var(--muted)">Address:</span> ${formatAddress(p)}</div>
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
//   ACCESS CONTROL (GRANT & REVOKE)
// ============================================================
function accessControlTableHTML(usersToRender) {
    if (!usersToRender || usersToRender.length === 0) {
        return `<p style="color:var(--muted); padding:10px;">No users found matching your search criteria.</p>`;
    }

    const { permissions, active_grants } = cache.accessControl;

    let html = `
        <div class="tbl-wrap">
            <table>
                <tr>
                    <th>User Name</th>
                    <th>Role</th>
                    <th>Permissions Status</th>
                </tr>
    `;

    usersToRender.forEach(u => {
        // Figure out which permissions this specific user owns
        const userHasPermIds = active_grants.filter(g => g.user_id == u.id).map(g => g.permission_id);

        let permControls = permissions.map(p => {
            const hasPerm = userHasPermIds.includes(p.id);
            if (hasPerm) {
                return `
                    <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(16,185,129,.12); padding:8px; margin-bottom:4px; border-radius:6px; border:1px solid rgba(16,185,129,.3);">
                        <span style="color:#6ee7b7;font-weight:600;"><i class="fas fa-check-circle" style="color:#10b981;"></i> ${p.name}</span>
                        <button class="btn btn-red btn-sm" onclick="revokeAccess(${u.id}, ${p.id})"><i class="fas fa-times"></i> Revoke</button>
                    </div>`;
            } else {
                return `
                    <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,.04); padding:8px; margin-bottom:4px; border-radius:6px; border:1px solid rgba(6,182,212,.15);">
                        <span style="color:#94b4d0;"><i class="fas fa-times-circle" style="color:#ef4444;opacity:.7;"></i> ${p.name}</span>
                        <button class="btn btn-blue btn-sm" onclick="grantAccess(${u.id}, ${p.id})"><i class="fas fa-check"></i> Grant</button>
                    </div>`;
            }
        }).join('');

        html += `
            <tr>
                <td><b>${u.name}</b><br><span style="font-size:12px;color:var(--muted)">ID: #${u.id}</span></td>
                <td><span class="pill pill-blue">${u.role}</span></td>
                <td style="min-width:300px;">${permControls}</td>
            </tr>
        `;
    });

    html += `</table></div>`;
    return html;
}

async function renderAccessControl(el) {
    // Fetch data and save it to the cache so we can filter it instantly without refreshing
    const data = await api('get_access_control', '', 'GET');
    if (data.error) return el.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
    cache.accessControl = data;

    // Dynamically get the roles that exist in the system for the dropdown
    const roles = [...new Set(data.users.map(u => u.role))];
    const roleOptions = roles.map(r => `<option value="${r}">${r}</option>`).join('');

    // Draw the outer UI with the search bar and filter dropdown
    el.innerHTML = `
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <h3 style="margin:0;"><i class="fas fa-lock-open"></i> Grant & Revoke Permissions</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select id="acRoleFilter" onchange="filterAccessControl()" style="width:160px; padding:8px;">
                        <option value="All">All Roles</option>
                        ${roleOptions}
                    </select>
                    <input type="text" id="acSearchInput" onkeyup="filterAccessControl()" placeholder="Search Name or ID..." style="width:220px; padding:8px;">
                </div>
            </div>
            <p style="color:var(--muted); margin-bottom:20px;">Manage fine-grained access control for staff and patients.</p>

            <div id="accessControlTableContainer">
                ${accessControlTableHTML(cache.accessControl.users)}
            </div>
        </div>
    `;
}

function filterAccessControl() {
    const roleFilter = document.getElementById('acRoleFilter').value;
    const query = document.getElementById('acSearchInput').value.toLowerCase();

    // Filter the cached users based on dropdown and text box
    const filteredUsers = cache.accessControl.users.filter(u => {
        const matchesRole = roleFilter === 'All' || u.role === roleFilter;
        const matchesSearch = query === '' || u.name.toLowerCase().includes(query) || String(u.id).includes(query);
        return matchesRole && matchesSearch;
    });

    // Instantly inject the filtered table back into the UI
    document.getElementById('accessControlTableContainer').innerHTML = accessControlTableHTML(filteredUsers);
}

async function grantAccess(userId, permId) {
    const res = await api('grant_permission', { user_id: userId, permission_id: permId });
    if (res.error) { alert(res.error); return; }
    // Update cache locally — no page reload needed
    if (!cache.accessControl.active_grants.some(g => g.user_id == userId && g.permission_id == permId)) {
        cache.accessControl.active_grants.push({ user_id: userId, permission_id: permId });
    }
    filterAccessControl(); // Re-render keeping current search/filter state
}

async function revokeAccess(userId, permId) {
    if (!confirm('Are you sure you want to revoke this permission?')) return;
    const res = await api('revoke_permission', { user_id: userId, permission_id: permId });
    if (res.error) { alert(res.error); return; }
    // Remove from cache locally — no page reload needed
    cache.accessControl.active_grants = cache.accessControl.active_grants.filter(
        g => !(g.user_id == userId && g.permission_id == permId)
    );
    filterAccessControl(); // Re-render keeping current search/filter state
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
        body = `
        <div id="editAlert"></div>
        <div class="form-group"><label>Full Name</label><input id="e_name" value="${data.Name||''}"></div>
        <div class="form-row col2">
            <div class="form-group" style="margin:0"><label>Date of Birth</label><input type="date" id="e_dob" value="${data.DateOfBirth||''}"></div>
            <div class="form-group" style="margin:0"><label>Gender</label>
                <select id="e_gender">
                    <option value="M" ${data.Gender==='M'?'selected':''}>Male</option>
                    <option value="F" ${data.Gender==='F'?'selected':''}>Female</option>
                    <option value="O" ${data.Gender==='O'?'selected':''}>Other</option>
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-top:10px;"><label>Phone</label><input id="e_phone" value="${data.Phone||''}"></div>
        <div class="form-group"><label>Street</label><input id="e_street" value="${data.Street||''}"></div>
        <div class="form-row col2">
            <div class="form-group" style="margin:0"><label>City</label><input id="e_city" value="${data.City||''}"></div>
            <div class="form-group" style="margin:0"><label>Zip Code</label><input id="e_zip" value="${data.ZipCode||''}"></div>
        </div>`;
    }
    else if (type === 'department') {
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
            dob: document.getElementById('e_dob').value,
            gender: document.getElementById('e_gender').value,
            phone: document.getElementById('e_phone').value.trim(),
            street: document.getElementById('e_street').value.trim(),
            city: document.getElementById('e_city').value.trim(),
            zipcode: document.getElementById('e_zip').value.trim(),
        };
        if (!payload.name) { document.getElementById('editAlert').innerHTML=`<div class="alert alert-error">Name required</div>`; return; }
    }
    else if (type === 'department') {
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

<div id="editModal" class="modal-overlay">
    <div class="modal-box" style="max-width:520px">
        <h2 id="editModalTitle"><i class="fas fa-edit" style="color:var(--teal)"></i> Edit Record</h2>
        <div id="editModalBody"></div>
        <div style="display:flex;gap:12px;margin-top:20px">
            <button class="btn btn-teal" style="flex:1;justify-content:center" onclick="submitEdit()"><i class="fas fa-save"></i> Save Changes</button>
            <button class="btn btn-red" style="flex:1;justify-content:center" onclick="closeEditModal()"><i class="fas fa-times"></i> Cancel</button>
        </div>
    </div>
</div>

</body>
</html>
