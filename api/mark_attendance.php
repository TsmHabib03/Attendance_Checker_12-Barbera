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
    
    if (empty($student_id)) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required']);
        exit;
    }
    
    // Check if student exists
    $student_query = "SELECT * FROM students WHERE student_id = :student_id";
    $student_stmt = $db->prepare($student_query);
    $student_stmt->bindParam(':student_id', $student_id);
    $student_stmt->execute();
    
    if ($student_stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if attendance already marked today
    $today = date('Y-m-d');
    $check_query = "SELECT * FROM attendance WHERE student_id = :student_id AND date = :today";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':student_id', $student_id);
    $check_stmt->bindParam(':today', $today);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => false, 
            'message' => 'Attendance already marked for today at ' . $existing['time']
        ]);
        exit;
    }
    
    // Determine if student is late (after 9:00 AM)
    $current_time = date('H:i:s');
    $late_threshold = '09:00:00';
    $status = ($current_time > $late_threshold) ? 'late' : 'present';
    
    // Mark attendance
    $insert_query = "INSERT INTO attendance (student_id, date, time, status) VALUES (:student_id, :date, :time, :status)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':student_id', $student_id);
    $insert_stmt->bindParam(':date', $today);
    $insert_stmt->bindParam(':time', $current_time);
    $insert_stmt->bindParam(':status', $status);
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Attendance marked successfully!',
            'student_id' => $student_id,
            'student_name' => $student['first_name'] . ' ' . $student['last_name'],
            'time' => date('h:i:s A'),
            'status' => ucfirst($status)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
