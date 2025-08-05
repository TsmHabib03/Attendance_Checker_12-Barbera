<?php
header('Content-Type: application/json');
require_once '../includes/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $today = date('Y-m-d');
    
    // Get today's attendance with student details
    $query = "SELECT a.*, s.first_name, s.last_name, s.class 
              FROM attendance a 
              JOIN students s ON a.student_id = s.student_id 
              WHERE a.date = :today 
              ORDER BY a.time ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format time for display
    foreach ($attendance as &$record) {
        $record['time'] = date('h:i:s A', strtotime($record['time']));
    }
    
    echo json_encode([
        'success' => true,
        'attendance' => $attendance,
        'date' => $today,
        'total' => count($attendance)
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
