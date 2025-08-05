<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - Attendance Checker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>👥 View All Students</h1>
            <p>Manage registered students and their QR codes</p>
        </header>

        <nav class="navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="register_student.php">Register Student</a></li>
                <li><a href="scan_attendance.php">Scan Attendance</a></li>
                <li><a href="view_students.php" class="active">View Students</a></li>
                <li><a href="attendance_report.php">Attendance Report</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="controls-section">
                <div class="search-controls">
                    <input type="text" id="search-input" placeholder="Search students..." class="form-control">
                    <select id="class-filter" class="form-control">
                        <option value="">All Classes</option>
                        <option value="12-A">12-A</option>
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
                <div class="action-controls">
                    <button onclick="exportStudents()" class="btn btn-success">Export CSV</button>
                    <button onclick="printStudents()" class="btn btn-primary">Print List</button>
                    <button onclick="loadStudents()" class="btn btn-primary">Refresh</button>
                </div>
            </div>

            <div id="students-container">
                <p>Loading students...</p>
            </div>

            <!-- Student Detail Modal -->
            <div id="student-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <div id="modal-body"></div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Attendance Checker System</p>
        </footer>
    </div>

    <script src="js/main.js"></script>
    <script>
        let allStudents = [];
        let filteredStudents = [];

        // Load all students
        async function loadStudents() {
            showLoading('students-container');
            
            try {
                const response = await fetch('api/get_students.php');
                const result = await response.json();
                
                if (result.success) {
                    allStudents = result.students;
                    filteredStudents = [...allStudents];
                    displayStudents(filteredStudents);
                } else {
                    document.getElementById('students-container').innerHTML = 
                        '<div class="alert alert-error">Error loading students: ' + result.message + '</div>';
                }
            } catch (error) {
                document.getElementById('students-container').innerHTML = 
                    '<div class="alert alert-error">Error loading students: ' + error.message + '</div>';
            }
        }

        // Display students in table format
        function displayStudents(students) {
            const container = document.getElementById('students-container');
            
            if (students.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No students found.</div>';
                return;
            }
            
            let html = `
                <div class="students-summary">
                    <h3>Total Students: ${students.length}</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            students.forEach(student => {
                html += `
                    <tr>
                        <td>${student.student_id}</td>
                        <td>${student.first_name} ${student.last_name}</td>
                        <td>${student.email}</td>
                        <td>${student.class}</td>
                        <td>${formatDate(student.created_at)}</td>
                        <td>
                            <button onclick="viewStudent('${student.student_id}')" class="btn btn-primary btn-small">View</button>
                            <button onclick="showQRCode('${student.student_id}')" class="btn btn-success btn-small">QR Code</button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        // Filter students
        function filterStudents() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const classFilter = document.getElementById('class-filter').value;
            
            filteredStudents = allStudents.filter(student => {
                const matchesSearch = !searchTerm || 
                    student.student_id.toLowerCase().includes(searchTerm) ||
                    student.first_name.toLowerCase().includes(searchTerm) ||
                    student.last_name.toLowerCase().includes(searchTerm) ||
                    student.email.toLowerCase().includes(searchTerm);
                
                const matchesClass = !classFilter || student.class === classFilter;
                
                return matchesSearch && matchesClass;
            });
            
            displayStudents(filteredStudents);
        }

        // View student details
        async function viewStudent(studentId) {
            try {
                const response = await fetch(`api/get_student_details.php?student_id=${studentId}`);
                const result = await response.json();
                
                if (result.success) {
                    const student = result.student;
                    const attendance = result.attendance;
                    
                    let modalContent = `
                        <h2>Student Details</h2>
                        <div class="student-info">
                            <p><strong>Student ID:</strong> ${student.student_id}</p>
                            <p><strong>Name:</strong> ${student.first_name} ${student.last_name}</p>
                            <p><strong>Email:</strong> ${student.email}</p>
                            <p><strong>Class:</strong> ${student.class}</p>
                            <p><strong>Registered:</strong> ${formatDate(student.created_at)}</p>
                        </div>
                        
                        <h3>QR Code</h3>
                        <div class="qr-code-display">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(student.qr_code)}" 
                                 alt="QR Code for ${student.student_id}">
                        </div>
                        
                        <h3>Recent Attendance</h3>
                    `;
                    
                    if (attendance.length > 0) {
                        modalContent += `
                            <table class="table">
                                <thead>
                                    <tr><th>Date</th><th>Time</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                        `;
                        attendance.forEach(record => {
                            modalContent += `
                                <tr>
                                    <td>${formatDate(record.date)}</td>
                                    <td>${formatTime(record.time)}</td>
                                    <td><span class="badge ${record.status === 'present' ? 'badge-success' : 'badge-warning'}">${record.status}</span></td>
                                </tr>
                            `;
                        });
                        modalContent += '</tbody></table>';
                    } else {
                        modalContent += '<p>No attendance records found.</p>';
                    }
                    
                    modalContent += `
                        <div class="modal-actions">
                            <button onclick="printStudentDetails('${studentId}')" class="btn btn-primary">Print Details</button>
                            <button onclick="closeModal()" class="btn btn-danger">Close</button>
                        </div>
                    `;
                    
                    document.getElementById('modal-body').innerHTML = modalContent;
                    document.getElementById('student-modal').style.display = 'block';
                } else {
                    showMessage('Error loading student details: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Error loading student details: ' + error.message, 'error');
            }
        }

        // Show QR code
        function showQRCode(studentId) {
            const student = allStudents.find(s => s.student_id === studentId);
            if (!student) return;
            
            const modalContent = `
                <h2>QR Code for ${student.first_name} ${student.last_name}</h2>
                <div class="qr-code-display">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(student.qr_code)}" 
                         alt="QR Code for ${student.student_id}">
                    <p><strong>Student ID:</strong> ${student.student_id}</p>
                    <p><strong>Class:</strong> ${student.class}</p>
                </div>
                <div class="modal-actions">
                    <button onclick="printQRCode('${studentId}')" class="btn btn-primary">Print QR Code</button>
                    <button onclick="closeModal()" class="btn btn-danger">Close</button>
                </div>
            `;
            
            document.getElementById('modal-body').innerHTML = modalContent;
            document.getElementById('student-modal').style.display = 'block';
        }

        // Export students to CSV
        function exportStudents() {
            if (filteredStudents.length === 0) {
                showMessage('No students to export', 'error');
                return;
            }
            
            const data = filteredStudents.map(student => ({
                'Student ID': student.student_id,
                'First Name': student.first_name,
                'Last Name': student.last_name,
                'Email': student.email,
                'Class': student.class,
                'Registered': student.created_at
            }));
            
            exportToCSV(data, 'students_list.csv');
        }

        // Print functions
        function printStudents() {
            printElement('students-container', 'Students List');
        }

        function printQRCode(studentId) {
            const student = allStudents.find(s => s.student_id === studentId);
            if (!student) return;
            
            const qrHtml = `
                <div class="qr-code-display">
                    <h2>${student.first_name} ${student.last_name}</h2>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(student.qr_code)}" 
                         alt="QR Code for ${student.student_id}">
                    <p><strong>Student ID:</strong> ${student.student_id}</p>
                    <p><strong>Class:</strong> ${student.class}</p>
                </div>
            `;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>QR Code - ${student.student_id}</title>
                    <style>
                        body { text-align: center; font-family: Arial, sans-serif; padding: 20px; }
                        .qr-code-display img { border: 2px solid #333; padding: 10px; }
                    </style>
                </head>
                <body>${qrHtml}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Modal functions
        function closeModal() {
            document.getElementById('student-modal').style.display = 'none';
        }

        // Event listeners
        document.getElementById('search-input').addEventListener('input', 
            debounce(filterStudents, 300));
        document.getElementById('class-filter').addEventListener('change', filterStudents);

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('student-modal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Load students on page load
        document.addEventListener('DOMContentLoaded', loadStudents);
    </script>

    <style>
        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-controls {
            display: flex;
            gap: 1rem;
            flex: 1;
        }

        .action-controls {
            display: flex;
            gap: 0.5rem;
        }

        .students-summary {
            margin-bottom: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 8px;
            text-align: center;
        }

        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 15px;
            top: 15px;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-actions {
            margin-top: 2rem;
            text-align: center;
        }

        .modal-actions .btn {
            margin: 0 0.5rem;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge-success {
            background-color: #48bb78;
            color: white;
        }

        .badge-warning {
            background-color: #ed8936;
            color: white;
        }

        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
            }
            
            .search-controls {
                flex-direction: column;
            }
            
            .action-controls {
                justify-content: center;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</body>
</html>
