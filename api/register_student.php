<?php
header('Content-Type: application/json');
require_once '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $student_id = $_POST['student_id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $class = $_POST['class'] ?? '';
    
    // Validate required fields
    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email) || empty($class)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Check if student ID already exists
    $check_query = "SELECT id FROM students WHERE student_id = :student_id OR email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':student_id', $student_id);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Student ID or email already exists']);
        exit;
    }
    
    // Generate QR code data (student ID + timestamp for uniqueness)
    $qr_data = $student_id . '|' . time();
    
    // Insert new student
    $query = "INSERT INTO students (student_id, first_name, last_name, email, class, qr_code) 
              VALUES (:student_id, :first_name, :last_name, :email, :class, :qr_code)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':class', $class);
    $stmt->bindParam(':qr_code', $qr_data);
    
    if ($stmt->execute()) {
        // Generate QR code image using Google Charts API
        $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_data);
        $qr_code_html = '<img src="' . $qr_code_url . '" alt="QR Code for ' . htmlspecialchars($student_id) . '">';
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student registered successfully!',
            'qr_code' => $qr_code_html,
            'student_id' => $student_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register student']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
