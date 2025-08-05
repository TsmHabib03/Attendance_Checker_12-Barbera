<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Checker System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Attendance Checker System</h1>
            <p>QR Code Based Attendance Management</p>
        </header>

        <nav class="navigation">
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="register_student.php">Register Student</a></li>
                <li><a href="scan_attendance.php">Scan Attendance</a></li>
                <li><a href="view_students.php">View Students</a></li>
                <li><a href="attendance_report.php">Attendance Report</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="welcome-section">
                <h2>Welcome to the Attendance System</h2>
                <p>This system allows you to manage student attendance using QR codes. Here's what you can do:</p>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>👤 Register Students</h3>
                        <p>Add new students to the system and generate unique QR codes for each student.</p>
                        <a href="register_student.php" class="btn btn-primary">Register Student</a>
                    </div>

                    <div class="feature-card">
                        <h3>📱 Scan QR Code</h3>
                        <p>Use your device's camera to scan student QR codes and mark attendance.</p>
                        <a href="scan_attendance.php" class="btn btn-primary">Scan Attendance</a>
                    </div>

                    <div class="feature-card">
                        <h3>👥 View Students</h3>
                        <p>See all registered students and their QR codes.</p>
                        <a href="view_students.php" class="btn btn-primary">View Students</a>
                    </div>

                    <div class="feature-card">
                        <h3>📊 Attendance Report</h3>
                        <p>View detailed attendance reports and statistics.</p>
                        <a href="attendance_report.php" class="btn btn-primary">View Reports</a>
                    </div>
                </div>
            </div>

            <div class="stats-section">
                <h3>Today's Summary</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Students</h4>
                        <span class="stat-number" id="totalStudents">0</span>
                    </div>
                    <div class="stat-card">
                        <h4>Present Today</h4>
                        <span class="stat-number" id="presentToday">0</span>
                    </div>
                    <div class="stat-card">
                        <h4>Attendance Rate</h4>
                        <span class="stat-number" id="attendanceRate">0%</span>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Attendance Checker System - Built with HTML, CSS, JavaScript, PHP & MySQL</p>
        </footer>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Load dashboard statistics
        loadDashboardStats();
    </script>
</body>
</html>
