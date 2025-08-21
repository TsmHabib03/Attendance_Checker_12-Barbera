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
            
            <div class="time-display" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <h4>Current Time (Philippines): <span id="current-time"></span></h4>
                <p id="current-schedule-info">Loading schedule info...</p>
            </div>
            
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
                <p>If camera scanning doesn't work, you can manually enter the LRN:</p>
                <form id="manual-form">
                    <div class="form-group">
                        <label for="manual-lrn">LRN (Learner Reference Number):</label>
                        <input type="text" id="manual-lrn" name="lrn" class="form-control" 
                               placeholder="e.g., 123456789012" pattern="[0-9]{11,13}" 
                               title="LRN must be 11-13 digits" maxlength="13" minlength="11">
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
                // Extract LRN from QR data (format: 123456789012|timestamp)
                const lrn = qrData.split('|')[0];
                
                // Mark attendance
                const response = await fetch('api/mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `lrn=${encodeURIComponent(lrn)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    document.getElementById('result-details').innerHTML = `
                        <div class="alert alert-success">
                            <h4>Attendance Recorded!</h4>
                            <p><strong>Student:</strong> ${result.student_name}</p>
                            <p><strong>LRN:</strong> ${result.lrn}</p>
                            <p><strong>Subject:</strong> ${result.subject}</p>
                            <p><strong>Period:</strong> ${result.period}</p>
                            <p><strong>Class Time:</strong> ${result.class_time}</p>
                            <p><strong>Scanned At:</strong> ${result.scan_time}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${result.status.toLowerCase() === 'present' ? 'success' : result.status.toLowerCase() === 'late' ? 'warning' : 'danger'}">${result.status}</span></p>
                            ${result.debug_info ? `<details><summary>Debug Info</summary><pre>${JSON.stringify(result.debug_info, null, 2)}</pre></details>` : ''}
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
            
            const lrn = document.getElementById('manual-lrn').value;
            
            if (!lrn) {
                showMessage('Please enter an LRN', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `lrn=${encodeURIComponent(lrn)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    document.getElementById('manual-lrn').value = '';
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
                        let html = '<table class="table"><thead><tr><th>LRN</th><th>Name</th><th>Subject</th><th>Period</th><th>Time</th><th>Status</th></tr></thead><tbody>';
                        result.attendance.forEach(record => {
                            const statusClass = 
                                record.status === 'present' ? 'badge-success' :
                                record.status === 'late' ? 'badge-warning' :
                                record.status === 'absent' ? 'badge-danger' :
                                'badge-secondary';
                            
                            html += `<tr>
                                <td>${record.lrn}</td>
                                <td>${record.first_name} ${record.last_name}</td>
                                <td>${record.subject}</td>
                                <td>${record.period_number}</td>
                                <td>${record.time}</td>
                                <td><span class="badge ${statusClass}">${record.status.toUpperCase()}</span></td>
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

        // Update current time and schedule info
        function updateTimeAndSchedule() {
            const now = new Date();
            const timeString = now.toLocaleString('en-PH', { 
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true 
            });
            
            document.getElementById('current-time').textContent = timeString;
            
            // Get current schedule info
            fetch('api/get_current_schedule.php?class=12-BARBERRA')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const scheduleInfo = document.getElementById('current-schedule-info');
                        if (data.current_period) {
                            const period = data.current_period;
                            scheduleInfo.innerHTML = `
                                <strong>Current Period:</strong> ${period.subject} 
                                (Period ${period.period_number}: ${period.start_time_formatted || ''} - ${period.end_time_formatted || ''})
                                ${period.is_break ? ' <span class="badge badge-warning">BREAK TIME</span>' : ''}
                            `;
                        } else {
                            scheduleInfo.innerHTML = `
                                <strong>No class scheduled now</strong> 
                                (${data.current_day} ${data.current_time})
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching schedule:', error);
                });
        }

        // Event listeners
        document.getElementById('start-scan').addEventListener('click', startScanning);
        document.getElementById('stop-scan').addEventListener('click', stopScanning);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeScanner();
            loadTodayAttendance();
            updateTimeAndSchedule();
            
            // Update time every second
            setInterval(updateTimeAndSchedule, 1000);
        });
    </script>
</body>
</html>
