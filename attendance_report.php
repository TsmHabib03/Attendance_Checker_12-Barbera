<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - Attendance Checker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Attendance Reports</h1>
            <p>View and analyze attendance data</p>
        </header>

        <nav class="navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="register_student.php">Register Student</a></li>
                <li><a href="scan_attendance.php">Scan Attendance</a></li>
                <li><a href="view_students.php">View Students</a></li>
                <li><a href="attendance_report.php" class="active">Attendance Report</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="report-controls">
                <div class="date-controls">
                    <label for="start-date">From:</label>
                    <input type="date" id="start-date" class="form-control">
                    
                    <label for="end-date">To:</label>
                    <input type="date" id="end-date" class="form-control">
                    
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
                    <button onclick="generateReport()" class="btn btn-primary">Generate Report</button>
                    <button onclick="exportReport()" class="btn btn-success">Export CSV</button>
                    <button onclick="printReport()" class="btn btn-primary">Print Report</button>
                </div>
            </div>

            <div id="report-summary" style="display: none;">
                <h3>Report Summary</h3>
                <div class="summary-grid">
                    <div class="summary-card">
                        <h4>Total Students</h4>
                        <span id="summary-total">0</span>
                    </div>
                    <div class="summary-card">
                        <h4>Total Present</h4>
                        <span id="summary-present">0</span>
                    </div>
                    <div class="summary-card">
                        <h4>Total Late</h4>
                        <span id="summary-late">0</span>
                    </div>
                    <div class="summary-card">
                        <h4>Attendance Rate</h4>
                        <span id="summary-rate">0%</span>
                    </div>
                </div>
            </div>

            <div id="report-content">
                <p>Select date range and click "Generate Report" to view attendance data.</p>
            </div>

            <div id="chart-container" style="display: none;">
                <h3>Attendance Chart</h3>
                <canvas id="attendance-chart" width="400" height="200"></canvas>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Attendance Checker System</p>
        </footer>
    </div>

    <!-- Include Chart.js for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/main.js"></script>
    <script>
        let currentReportData = [];
        let attendanceChart = null;

        // Set default dates (last 7 days)
        function setDefaultDates() {
            const today = new Date();
            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            
            document.getElementById('end-date').value = today.toISOString().split('T')[0];
            document.getElementById('start-date').value = weekAgo.toISOString().split('T')[0];
        }

        // Generate attendance report
        async function generateReport() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const classFilter = document.getElementById('class-filter').value;
            
            if (!startDate || !endDate) {
                showMessage('Please select both start and end dates', 'error');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                showMessage('Start date cannot be after end date', 'error');
                return;
            }
            
            showLoading('report-content');
            
            try {
                const params = new URLSearchParams({
                    start_date: startDate,
                    end_date: endDate,
                    class: classFilter
                });
                
                const response = await fetch(`api/get_attendance_report.php?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    currentReportData = result.data;
                    displayReport(result);
                    updateSummary(result.summary);
                    createChart(result.chart_data);
                } else {
                    document.getElementById('report-content').innerHTML = 
                        '<div class="alert alert-error">Error generating report: ' + result.message + '</div>';
                }
            } catch (error) {
                document.getElementById('report-content').innerHTML = 
                    '<div class="alert alert-error">Error generating report: ' + error.message + '</div>';
            }
        }

        // Display report data
        function displayReport(result) {
            const container = document.getElementById('report-content');
            const data = result.data;
            
            if (data.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No attendance records found for the selected period.</div>';
                return;
            }
            
            let html = `
                <h3>Attendance Records</h3>
                <table class="table" id="report-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>LRN</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Period</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.forEach(record => {
                const statusClass = 
                    record.status === 'present' ? 'badge-success' :
                    record.status === 'late' ? 'badge-warning' :
                    record.status === 'absent' ? 'badge-danger' :
                    'badge-secondary';
                
                html += `
                    <tr>
                        <td>${formatDate(record.date)}</td>
                        <td>${record.lrn}</td>
                        <td>${record.first_name} ${record.last_name}</td>
                        <td>${record.class}</td>
                        <td>${record.subject}</td>
                        <td>${record.period_number || 'N/A'}</td>
                        <td>${formatTime(record.time)}</td>
                        <td><span class="badge ${statusClass}">${record.status.toUpperCase()}</span></td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        // Update summary statistics
        function updateSummary(summary) {
            document.getElementById('summary-total').textContent = summary.total_records;
            document.getElementById('summary-present').textContent = summary.present_count;
            document.getElementById('summary-late').textContent = summary.late_count;
            document.getElementById('summary-rate').textContent = summary.attendance_rate + '%';
            
            document.getElementById('report-summary').style.display = 'block';
        }

        // Create attendance chart
        function createChart(chartData) {
            const ctx = document.getElementById('attendance-chart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (attendanceChart) {
                attendanceChart.destroy();
            }
            
            attendanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.dates,
                    datasets: [{
                        label: 'Present',
                        data: chartData.present,
                        borderColor: 'rgb(72, 187, 120)',
                        backgroundColor: 'rgba(72, 187, 120, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Late',
                        data: chartData.late,
                        borderColor: 'rgb(237, 137, 54)',
                        backgroundColor: 'rgba(237, 137, 54, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Daily Attendance Trend'
                        }
                    }
                }
            });
            
            document.getElementById('chart-container').style.display = 'block';
        }

        // Export report to CSV
        function exportReport() {
            if (currentReportData.length === 0) {
                showMessage('No data to export. Please generate a report first.', 'error');
                return;
            }
            
            const data = currentReportData.map(record => ({
                'Date': record.date,
                'LRN': record.lrn,
                'First Name': record.first_name,
                'Last Name': record.last_name,
                'Class': record.class,
                'Time': record.time,
                'Status': record.status
            }));
            
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const filename = `attendance_report_${startDate}_to_${endDate}.csv`;
            
            exportToCSV(data, filename);
        }

        // Print report
        function printReport() {
            if (currentReportData.length === 0) {
                showMessage('No data to print. Please generate a report first.', 'error');
                return;
            }
            
            printElement('report-content', 'Attendance Report');
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setDefaultDates();
        });
    </script>

    <style>
        .report-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .date-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .date-controls label {
            font-weight: 500;
            color: #4a5568;
        }

        .action-controls {
            display: flex;
            gap: 0.5rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
        }

        .summary-card h4 {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .summary-card span {
            font-size: 2rem;
            font-weight: bold;
        }

        #chart-container {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        #attendance-chart {
            max-height: 400px;
        }

        @media (max-width: 768px) {
            .report-controls {
                flex-direction: column;
            }
            
            .date-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-controls {
                justify-content: center;
            }
        }
    </style>
</body>
</html>
