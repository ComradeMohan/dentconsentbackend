<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db_connect.php';

// Handle both JSON and Multipart (Retrofit uses Multipart for registration with image)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    if (empty($data)) {
        $data = json_decode(file_get_contents('php://input'), true);
    }

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? 'patient';
    $fullName = $data['full_name'] ?? '';

    if (empty($email) || empty($password) || empty($fullName)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email, password, and full name are required']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Handle Profile Image if present
        $profileImagePath = null;
        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Generate hashed filename
            $hashed_filename = md5($email . time() . rand()) . '.' . $file_extension;
            $upload_dir = 'uploads/profile_images/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $hashed_filename;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $profileImagePath = $upload_path;
            }
        }

        // 2. Create User
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, profile_image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $passwordHash, $role, $profileImagePath]);
        $userId = $pdo->lastInsertId();

        // 3. Create Role-Specific Profile
        if ($role === 'doctor') {
            $councilId = $data['council_id'] ?? '';
            $specialization = $data['specialization'] ?? '';
            $mobileNumber = $data['mobile_number'] ?? '';
            
            // Doctor DOB formatting
            $dobRaw = $data['dob'] ?? '01-01-2000';
            $dob = $dobRaw;
            $separator = (strpos($dobRaw, '-') !== false) ? '-' : ((strpos($dobRaw, '/') !== false) ? '/' : null);
            
            if ($separator) {
                $parts = explode($separator, $dobRaw);
                if (count($parts) === 3) {
                    $dob = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            }
            
            $gender = $data['gender'] ?? 'Other';
            $qualifications = $data['qualifications'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO doctor_profiles (user_id, full_name, mobile_number, council_id, specialization, gender, dob, qualifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $fullName, $mobileNumber, $councilId, $specialization, $gender, $dob, $qualifications]);
        } else {
            // Patient DOB formatting: handle DD-MM-YYYY or DD/MM/YYYY
            $dobRaw = $data['dob'] ?? '01-01-2000';
            $dob = $dobRaw;
            $separator = (strpos($dobRaw, '-') !== false) ? '-' : ((strpos($dobRaw, '/') !== false) ? '/' : null);
            
            if ($separator) {
                $parts = explode($separator, $dobRaw);
                if (count($parts) === 3) {
                    $dob = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            }
            
            $gender = $data['gender'] ?? 'Other';
            $mobileNumber = $data['mobile_number'] ?? '';
            $address = $data['residential_address'] ?? '';
            $city = $data['city'] ?? '';
            $state = $data['state'] ?? '';
            $pincode = $data['pincode'] ?? '';
            $allergies = $data['allergies'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO patient_profiles (user_id, full_name, mobile_number, dob, gender, residential_address, city, state, pincode, allergies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $fullName, $mobileNumber, $dob, $gender, $address, $city, $state, $pincode, $allergies]);

            // Handle Medical Conditions
            $medicalConditions = $data['medical_conditions'] ?? [];
            if (!is_array($medicalConditions) && !empty($medicalConditions)) {
                $medicalConditions = explode(',', $medicalConditions);
            }
            
            if (!empty($medicalConditions)) {
                $stmt = $pdo->prepare("INSERT INTO patient_medical_conditions (patient_id, condition_name) VALUES (?, ?)");
                foreach ($medicalConditions as $condition) {
                    if (!empty(trim($condition))) {
                        $stmt->execute([$userId, trim($condition)]);
                    }
                }
            }
        }

        $pdo->commit();

        // Fast Response to Client
        ob_start();
        echo json_encode(['success' => true, 'user_id' => (int)$userId, 'message' => 'Registration successful']);
        $size = ob_get_length();
        header("Content-Length: $size");
        header('Connection: close');
        ob_end_flush();
        @ob_flush();
        flush();
        if (session_id()) session_write_close();
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

        // Send Welcome Email Asynchronously
        require_once 'email_service.php';
        $emailService = new EmailService();
        $emailService->sendWelcomeEmail($email, $fullName, $role);

    } catch (PDOException $e) {
        $pdo->rollBack();
        if (!headers_sent()) {
            http_response_code(500);
            // Handle common errors like duplicate email
            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'error' => 'Email or Council ID already exists']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()]);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
