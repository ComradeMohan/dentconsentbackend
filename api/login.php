<?php
require_once '../db_connect.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT u.id, u.email, u.password_hash, u.role, u.profile_image FROM users u WHERE u.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Fetch profile info based on role
        $profile = null;
        if ($user['role'] === 'doctor') {
            $stmt = $pdo->prepare("SELECT full_name, mobile_number, gender, dob, council_id, specialization, experience_years, qualifications, signature_url FROM doctor_profiles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch();
        } else {
            $stmt = $pdo->prepare("SELECT full_name, mobile_number, dob, gender, residential_address, city, state, pincode, allergies FROM patient_profiles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch();

            if ($profile) {
                // Fetch medical conditions
                $stmt = $pdo->prepare("SELECT condition_name FROM patient_medical_conditions WHERE patient_id = ?");
                $stmt->execute([$user['id']]);
                $conditions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $profile['medical_conditions'] = implode(', ', $conditions);
            }
        }

        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Merge user and profile data
        $userData = [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'profile_image' => $user['profile_image']
        ];

        if ($profile) {
            $userData = array_merge($userData, $profile);
        }

        echo json_encode([
            'success' => true,
            'user' => $userData
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
}
?>
