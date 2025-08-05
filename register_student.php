<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student - Attendance Checker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Register New Student</h1>
            <p>Add a new student to the attendance system</p>
        </header>

        <nav class="navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="register_student.php" class="active">Register Student</a></li>
                <li><a href="scan_attendance.php">Scan Attendance</a></li>
                <li><a href="view_students.php">View Students</a></li>
                <li><a href="attendance_report.php">Attendance Report</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div id="message"></div>
            
            <form id="studentForm" class="student-form">
                <div class="form-group">
                    <label for="student_id">Student ID:</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" required 
                           placeholder="e.g., STU001" pattern="[A-Z]{3}[0-9]{3}" 
                           title="Format: 3 uppercase letters followed by 3 numbers (e.g., STU001)">
                </div>

                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required 
                           placeholder="Enter first name">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required 
                           placeholder="Enter last name">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="student@school.com">
                </div>

                <div class="form-group">
                    <label for="class">Class:</label>
                    <select id="class" name="class" class="form-control" required>
                        <option value="">Select Class</option>
                        <option value="12-A">12-Barbera</option>
                        <option value="12-B">12-B</option>
                        <option value="12-C">12-C</option>
                        <option value="11-A">11-A</option>
                        <option value="11-B">11-B</option>
                        <option value="11-C">11-C</option>
                        <option value="10-A">10-A</option>
                        <option value="10-B">10-B</option>
                        <option value="10-C">10-C</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span id="submit-text">Register Student</span>
                    <span id="loading" class="loading" style="display: none;"></span>
                </button>
            </form>

            <div id="qr-result" style="display: none;">
                <h3>Student Registered Successfully!</h3>
                <p>QR Code generated for the student:</p>
                <div class="qr-code-display" id="qr-display"></div>
                <button onclick="printQR()" class="btn btn-success">Print QR Code</button>
                <button onclick="resetForm()" class="btn btn-primary">Register Another Student</button>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Attendance Checker System</p>
        </footer>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.getElementById('studentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.querySelector('button[type="submit"]');
            const submitText = document.getElementById('submit-text');
            const loading = document.getElementById('loading');
            const messageDiv = document.getElementById('message');
            
            // Show loading state
            submitText.style.display = 'none';
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('api/register_student.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                    document.getElementById('studentForm').style.display = 'none';
                    document.getElementById('qr-result').style.display = 'block';
                    document.getElementById('qr-display').innerHTML = result.qr_code;
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="alert alert-error">Error: ' + error.message + '</div>';
            } finally {
                // Reset loading state
                submitText.style.display = 'inline-block';
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
        
        function resetForm() {
            document.getElementById('studentForm').reset();
            document.getElementById('studentForm').style.display = 'block';
            document.getElementById('qr-result').style.display = 'none';
            document.getElementById('message').innerHTML = '';
        }
        
        function printQR() {
            const qrContent = document.getElementById('qr-display').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Student QR Code</title>
                    <style>
                        body { text-align: center; font-family: Arial, sans-serif; padding: 20px; }
                        .qr-code { margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <h2>Student QR Code</h2>
                    <div class="qr-code">${qrContent}</div>
                    <p>Please keep this QR code safe for attendance scanning.</p>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>
