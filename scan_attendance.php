<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Attendance - Attendance Checker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📱 Scan QR Code for Attendance</h1>
            <p>Point your camera at a student's QR code to mark attendance</p>
        </header>

        <nav class="navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="register_student.php">Register Student</a></li>
                <li><a href="scan_attendance.php" class="active">Scan Attendance</a></li>
                <li><a href="view_students.php">View Students</a></li>
                <li><a href="attendance_report.php">Attendance Report</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div id="message"></div>
            
            <div class="scanner-container">
                <div class="scanner-status">
                    <h3>Camera Status: <span id="camera-status">Not Started</span></h3>
                </div>
                
                <video id="video" style="display: none;"></video>
                <canvas id="canvas" style="display: none;"></canvas>
                
                <div class="scanner-controls">
                    <button id="start-scan" class="btn btn-primary">Start Camera</button>
                    <button id="stop-scan" class="btn btn-danger" style="display: none;">Stop Camera</button>
                </div>
                
                <div id="scan-result" style="display: none;">
                    <h3>Scan Result</h3>
                    <div id="result-details"></div>
                </div>
            </div>

            <div class="manual-entry">
                <h3>Manual Entry</h3>
                <p>If camera scanning doesn't work, you can manually enter the student ID:</p>
                <form id="manual-form">
                    <div class="form-group">
                        <label for="manual-student-id">Student ID:</label>
                        <input type="text" id="manual-student-id" name="student_id" class="form-control" 
                               placeholder="e.g., STU001" pattern="[A-Z]{3}[0-9]{3}">
                    </div>
                    <button type="submit" class="btn btn-success">Mark Attendance</button>
                </form>
            </div>

            <div class="today-attendance">
                <h3>Today's Attendance</h3>
                <div id="today-list">
                    <p>Loading today's attendance...</p>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Attendance Checker System</p>
        </footer>
    </div>

    <!-- Include ZXing library for QR code scanning -->
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        let codeReader = null;
        let selectedDeviceId = null;

        // Initialize QR code scanner
        async function initializeScanner() {
            try {
                codeReader = new ZXing.BrowserQRCodeReader();
                console.log('QR Code scanner initialized');
                
                // Get available video devices
                const videoInputDevices = await codeReader.listVideoInputDevices();
                if (videoInputDevices.length > 0) {
                    selectedDeviceId = videoInputDevices[0].deviceId;
                    console.log('Found camera devices:', videoInputDevices.length);
                } else {
                    throw new Error('No camera devices found');
                }
            } catch (error) {
                console.error('Error initializing scanner:', error);
                showMessage('Error: Could not access camera. Please ensure camera permissions are granted.', 'error');
            }
        }

        // Start scanning
        async function startScanning() {
            if (!codeReader) {
                await initializeScanner();
            }
            
            try {
                const video = document.getElementById('video');
                video.style.display = 'block';
                
                document.getElementById('start-scan').style.display = 'none';
                document.getElementById('stop-scan').style.display = 'inline-block';
                document.getElementById('camera-status').textContent = 'Starting...';
                
                // Start scanning
                const result = await codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                    if (result) {
                        console.log('QR Code detected:', result.text);
                        handleQRCodeResult(result.text);
                    }
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error('Scanning error:', err);
                    }
                });
                
                document.getElementById('camera-status').textContent = 'Scanning...';
                
            } catch (error) {
                console.error('Error starting scanner:', error);
                showMessage('Error starting camera: ' + error.message, 'error');
                resetScanner();
            }
        }

        // Stop scanning
        function stopScanning() {
            if (codeReader) {
                codeReader.reset();
            }
            resetScanner();
        }

        // Reset scanner UI
        function resetScanner() {
            document.getElementById('video').style.display = 'none';
            document.getElementById('start-scan').style.display = 'inline-block';
            document.getElementById('stop-scan').style.display = 'none';
            document.getElementById('camera-status').textContent = 'Not Started';
        }

        // Handle QR code scan result
        async function handleQRCodeResult(qrData) {
            console.log('Processing QR data:', qrData);
            
            // Stop scanning temporarily
            if (codeReader) {
                codeReader.reset();
            }
            
            try {
                // Extract student ID from QR data (format: STU001|timestamp)
                const studentId = qrData.split('|')[0];
                
                // Mark attendance
                const response = await fetch('api/mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `student_id=${encodeURIComponent(studentId)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    document.getElementById('result-details').innerHTML = `
                        <div class="alert alert-success">
                            <h4>Attendance Marked!</h4>
                            <p><strong>Student:</strong> ${result.student_name}</p>
                            <p><strong>ID:</strong> ${result.student_id}</p>
                            <p><strong>Time:</strong> ${result.time}</p>
                            <p><strong>Status:</strong> ${result.status}</p>
                        </div>
                    `;
                    document.getElementById('scan-result').style.display = 'block';
                    
                    // Refresh today's attendance list
                    loadTodayAttendance();
                } else {
                    showMessage(result.message, 'error');
                }
                
            } catch (error) {
                console.error('Error marking attendance:', error);
                showMessage('Error processing QR code: ' + error.message, 'error');
            }
            
            // Resume scanning after 3 seconds
            setTimeout(() => {
                if (document.getElementById('stop-scan').style.display !== 'none') {
                    startScanning();
                }
            }, 3000);
        }

        // Manual attendance marking
        document.getElementById('manual-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const studentId = document.getElementById('manual-student-id').value;
            
            if (!studentId) {
                showMessage('Please enter a student ID', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `student_id=${encodeURIComponent(studentId)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    document.getElementById('manual-student-id').value = '';
                    loadTodayAttendance();
                } else {
                    showMessage(result.message, 'error');
                }
                
            } catch (error) {
                showMessage('Error marking attendance: ' + error.message, 'error');
            }
        });

        // Load today's attendance
        async function loadTodayAttendance() {
            try {
                const response = await fetch('api/get_today_attendance.php');
                const result = await response.json();
                
                if (result.success) {
                    const attendanceList = document.getElementById('today-list');
                    if (result.attendance.length > 0) {
                        let html = '<table class="table"><thead><tr><th>Student ID</th><th>Name</th><th>Time</th><th>Status</th></tr></thead><tbody>';
                        result.attendance.forEach(record => {
                            html += `<tr>
                                <td>${record.student_id}</td>
                                <td>${record.first_name} ${record.last_name}</td>
                                <td>${record.time}</td>
                                <td><span class="badge ${record.status === 'present' ? 'badge-success' : 'badge-warning'}">${record.status}</span></td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        attendanceList.innerHTML = html;
                    } else {
                        attendanceList.innerHTML = '<p>No attendance records for today.</p>';
                    }
                }
            } catch (error) {
                console.error('Error loading today\'s attendance:', error);
            }
        }

        // Event listeners
        document.getElementById('start-scan').addEventListener('click', startScanning);
        document.getElementById('stop-scan').addEventListener('click', stopScanning);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeScanner();
            loadTodayAttendance();
        });
    </script>
</body>
</html>
