<?php
header('Content-Type: application/json');
require_once '../includes/database.php';

if (!isset($_GET['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $student_id = $_GET['student_id'];
    
    // Get student details
    $student_query = "SELECT * FROM students WHERE student_id = :student_id";
    $student_stmt = $db->prepare($student_query);
    $student_stmt->bindParam(':student_id', $student_id);
    $student_stmt->execute();
    
    if ($student_stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent attendance (last 30 days)
    $attendance_query = "SELECT * FROM attendance WHERE student_id = :student_id 
                        ORDER BY date DESC, time DESC LIMIT 30";
    $attendance_stmt = $db->prepare($attendance_query);
    $attendance_stmt->bindParam(':student_id', $student_id);
    $attendance_stmt->execute();
    
    $attendance = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'student' => $student,
        'attendance' => $attendance
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
